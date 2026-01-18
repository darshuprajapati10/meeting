<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\UsageTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function getCurrentSubscription(Organization $organization): Subscription
    {
        // Try to get active or trial subscription using the relationship method
        $subscription = $organization->subscription;
        
        // If no active subscription exists, check for trial subscriptions
        if (!$subscription) {
            $subscription = Subscription::where('organization_id', $organization->id)
                ->whereIn('status', ['active', 'trial'])
                ->latest()
                ->first();
        }
        
        // Check if current subscription is an expired trial
        if ($subscription && $subscription->status === 'trial' && $subscription->trial_ends_at && $subscription->trial_ends_at->isPast()) {
            // Trial has expired, downgrade to Free
            $this->downgradeExpiredTrial($subscription);
            // Get the new Free subscription (avoid recursion by directly querying)
            $subscription = Subscription::where('organization_id', $organization->id)
                ->whereIn('status', ['active', 'trial'])
                ->latest()
                ->first();
            
            // If still no subscription, create a free one
            if (!$subscription) {
                $subscription = $this->createFreeSubscription($organization);
            }
        }
        
        // If no subscription exists at all, create a free one
        if (!$subscription) {
            $subscription = $this->createFreeSubscription($organization);
        }
        
        // Always load plan relationship to prevent N+1 queries and null errors
        if (!$subscription->relationLoaded('plan')) {
            $subscription->load('plan');
        }
        
        return $subscription;
    }

    private function downgradeExpiredTrial(Subscription $trialSubscription): void
    {
        try {
            // Cancel the expired trial subscription
            $trialSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Create a new Free plan subscription for the organization
            $this->createFreeSubscription($trialSubscription->organization);

            Log::info('Expired trial subscription downgraded to Free', [
                'subscription_id' => $trialSubscription->id,
                'organization_id' => $trialSubscription->organization_id,
                'trial_ended_at' => $trialSubscription->trial_ends_at,
            ]);
        } catch (\Exception $e) {
            Log::error('Error downgrading expired trial subscription', [
                'subscription_id' => $trialSubscription->id,
                'organization_id' => $trialSubscription->organization_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function createFreeSubscription(Organization $organization): Subscription
    {
        $freePlan = SubscriptionPlan::where('name', 'free')->first();

        if (!$freePlan) {
            throw new \Exception('Free plan not found. Please run the seeder.');
        }

        return Subscription::create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $freePlan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'starts_at' => now(),
        ]);
    }

    public function createProTrialSubscription(Organization $organization): Subscription
    {
        $proPlan = SubscriptionPlan::where('name', 'pro')->first();

        if (!$proPlan) {
            throw new \Exception('Pro plan not found. Please run the seeder.');
        }

        $trialEndsAt = now()->addDays(89);

        return Subscription::create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $proPlan->id,
            'billing_cycle' => 'monthly',
            'status' => 'trial',
            'starts_at' => now(),
            'trial_ends_at' => $trialEndsAt,
            'ends_at' => null,
        ]);
    }

    public function checkLimit(Organization $organization, string $action, int $count = 1): array
    {
        try {
            $subscription = $this->getCurrentSubscription($organization);
            
            // Load plan relationship if not loaded
            if (!$subscription->relationLoaded('plan')) {
                $subscription->load('plan');
            }
            
            // Check if plan exists
            if (!$subscription->plan) {
                Log::error('Plan not found for subscription in checkLimit', [
                    'subscription_id' => $subscription->id,
                    'organization_id' => $organization->id,
                ]);
                // Return unlimited if plan is missing (graceful degradation)
                return ['allowed' => true, 'message' => null];
            }
            
            $limits = $subscription->plan->limits ?? [];
            $usage = $this->getCurrentUsage($organization);

        $limitMap = [
            'create_meeting' => ['limit' => 'meetings_per_month', 'usage' => 'meetings'],
            'add_attendee' => ['limit' => 'attendees_per_meeting', 'usage' => null],
            'create_contact' => ['limit' => 'contacts', 'usage' => 'contacts'],
            'create_survey' => ['limit' => 'active_surveys', 'usage' => 'surveys'],
            'survey_response' => ['limit' => 'survey_responses_per_month', 'usage' => 'responses'],
            'invite_user' => ['limit' => 'users', 'usage' => 'users'],
            'upload_file' => ['limit' => 'storage_mb', 'usage' => 'storage'],
        ];

        if (!isset($limitMap[$action])) {
            return ['allowed' => true, 'message' => null];
        }

        $config = $limitMap[$action];
        $limit = $limits[$config['limit']] ?? -1;

        // -1 means unlimited
        if ($limit === -1) {
            return ['allowed' => true, 'message' => null];
        }

            $currentUsage = $usage[$config['usage']] ?? 0;
            $allowed = ($currentUsage + $count) <= $limit;

            return [
                'allowed' => $allowed,
                'current' => $currentUsage,
                'limit' => $limit,
                'remaining' => max(0, $limit - $currentUsage),
                'message' => $allowed ? null : $this->getLimitMessage($action, $limit),
            ];
        } catch (\Exception $e) {
            Log::error('Error checking subscription limit', [
                'organization_id' => $organization->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return unlimited on error (graceful degradation)
            return ['allowed' => true, 'message' => null];
        }
    }

    public function getCurrentUsage(Organization $organization): array
    {
        $periodStart = now()->startOfMonth();

        return [
            'meetings' => $organization->meetings()
                ->where('created_at', '>=', $periodStart)
                ->count(),
            'contacts' => $organization->contacts()->count(),
            'surveys' => $organization->surveys()
                ->whereRaw('LOWER(status) = ?', ['active'])
                ->count(),
            'responses' => $this->getSurveyResponsesCount($organization, $periodStart),
            'users' => $organization->users()->count(),
            'storage' => $this->calculateStorageUsage($organization),
        ];
    }

    public function getSurveyResponsesCount(Organization $organization, Carbon $periodStart): int
    {
        try {
            // Count responses from survey_responses table for this month
            $responses = \DB::table('survey_responses')
                ->join('surveys', 'survey_responses.survey_id', '=', 'surveys.id')
                ->where('surveys.organization_id', $organization->id)
                ->where('survey_responses.created_at', '>=', $periodStart)
                ->count();

            // Also count unique submissions per meeting from survey_submissions table
            // Use selectRaw with COUNT(DISTINCT) for MySQL compatibility
            $submissions = \DB::table('survey_submissions')
                ->join('surveys', 'survey_submissions.survey_id', '=', 'surveys.id')
                ->where('surveys.organization_id', $organization->id)
                ->where('survey_submissions.created_at', '>=', $periodStart)
                ->whereNotNull('survey_submissions.meeting_id')
                ->selectRaw('COUNT(DISTINCT survey_submissions.meeting_id) as count')
                ->value('count') ?? 0;

            return (int) $responses + (int) $submissions;
        } catch (\Exception $e) {
            \Log::error('Error counting survey responses', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    public function calculateStorageUsage(Organization $organization): int
    {
        try {
            // Calculate in MB from survey_attachments table
            $totalBytes = $organization->surveyAttachments()->sum('size') ?? 0;
            return (int) ($totalBytes / (1024 * 1024));
        } catch (\Exception $e) {
            \Log::error('Error calculating storage usage', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    private function getLimitMessage(string $action, int $limit): string
    {
        $messages = [
            'create_meeting' => "You've reached your limit of {$limit} meetings this month. Upgrade to Pro for unlimited meetings.",
            'create_contact' => "You've reached your limit of {$limit} contacts. Upgrade to Pro for unlimited contacts.",
            'create_survey' => "You can only have {$limit} active survey on the Free plan. Upgrade to Pro for unlimited surveys.",
            'survey_response' => "You've reached your limit of {$limit} survey responses this month. Upgrade to Pro for unlimited responses.",
            'invite_user' => "Free plan allows only 1 user. Upgrade to Pro to add team members.",
            'upload_file' => "You've used your {$limit}MB storage limit. Upgrade to Pro for 10GB storage.",
        ];

        return $messages[$action] ?? "You've reached your plan limit. Upgrade to continue.";
    }

    public function hasFeature(Organization $organization, string $feature): bool
    {
        try {
            $subscription = $this->getCurrentSubscription($organization);
            
            // Load plan relationship if not loaded
            if (!$subscription->relationLoaded('plan')) {
                $subscription->load('plan');
            }
            
            // Check if plan exists
            if (!$subscription->plan) {
                Log::error('Plan not found for subscription in hasFeature', [
                    'subscription_id' => $subscription->id,
                    'organization_id' => $organization->id,
                ]);
                return false;
            }
            
            $features = $subscription->plan->features ?? [];
            return $features[$feature] ?? false;
        } catch (\Exception $e) {
            Log::error('Error checking feature', [
                'organization_id' => $organization->id,
                'feature' => $feature,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    public function incrementUsage(Organization $organization, string $metric, int $amount = 1): void
    {
        UsageTracking::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'metric' => $metric,
                'period_start' => now()->startOfMonth(),
            ],
            [
                'period_end' => now()->endOfMonth(),
            ]
        )->increment('count', $amount);
    }

    public function getAvailablePlans(): array
    {
        return SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }
}

