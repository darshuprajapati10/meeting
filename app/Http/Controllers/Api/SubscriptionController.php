<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use App\Services\RazorpayService;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\SubscriptionAddOn;
use App\Models\OrganizationAddOn;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Errors\Error as RazorpayError;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private RazorpayService $razorpayService
    ) {}

    public function plans(Request $request)
    {
        try {
            $plans = SubscriptionPlan::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($plan) {
                    return [
                        'id' => (string) $plan->id,
                        'name' => strtoupper($plan->display_name), // 'FREE' or 'PRO'
                        'slug' => $plan->name, // 'free' or 'pro'
                        'description' => $plan->description,
                        'monthly_price_paise' => $plan->price_monthly,
                        'yearly_price_paise' => $plan->price_yearly,
                        'limits' => $this->formatLimits($plan->limits),
                        'features' => $this->formatFeatures($plan->features),
                        'calendar_views' => $plan->features['calendar_views'] ?? ['month'],
                        'is_popular' => $plan->name === 'pro',
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Subscription plans retrieved successfully',
                'data' => $plans,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving subscription plans. Please try again later.',
            ], 500);
        }
    }

    private function formatLimits(array $limits): array
    {
        return [
            'meetings' => $limits['meetings_per_month'] ?? -1,
            'contacts' => $limits['contacts'] ?? -1,
            'active_surveys' => $limits['active_surveys'] ?? -1,
            'survey_responses' => $limits['survey_responses_per_month'] ?? -1,
            'users' => $limits['users'] ?? -1,
            'storage_bytes' => isset($limits['storage_mb']) 
                ? ($limits['storage_mb'] === -1 ? -1 : $limits['storage_mb'] * 1024 * 1024)
                : ($limits['storage_bytes'] ?? 1073741824),
        ];
    }

    private function formatFeatures(array $features): array
    {
        $featureList = [];
        
        // Always include basic features
        $featureList[] = 'basic_calendar';
        $featureList[] = 'meeting_management';
        $featureList[] = 'contact_management';
        $featureList[] = 'basic_surveys';
        
        // Add advanced features based on feature flags
        if (isset($features['csv_import']) && $features['csv_import']) {
            $featureList[] = 'csv_import';
        }
        if (isset($features['csv_export']) && $features['csv_export']) {
            $featureList[] = 'csv_export';
        }
        if (isset($features['survey_analytics']) && $features['survey_analytics'] === 'advanced') {
            $featureList[] = 'analytics';
        }
        if (isset($features['calendar_views']) && in_array('week', $features['calendar_views'])) {
            $featureList[] = 'week_view';
        }
        if (isset($features['calendar_views']) && in_array('day', $features['calendar_views'])) {
            $featureList[] = 'day_view';
        }
        if (isset($features['support_priority']) && $features['support_priority'] === 'priority') {
            $featureList[] = 'priority_support';
        }
        
        return array_unique($featureList);
    }

    public function current(Request $request)
    {
        try {
            $user = $request->user();
            $organization = $user->organization();
            
            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization not found.',
                ], 404);
            }

            // Get or create FREE subscription if none exists
            $subscription = $this->subscriptionService->getCurrentSubscription($organization);
            
            // Load plan relationship if not loaded
            if (!$subscription->relationLoaded('plan')) {
                $subscription->load('plan');
            }
            
            // Check if plan exists
            if (!$subscription->plan) {
                Log::error('Plan not found for subscription in current endpoint', [
                    'subscription_id' => $subscription->id,
                    'organization_id' => $organization->id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription plan not found. Please contact support.',
                ], 500);
            }
            
            $plan = $subscription->plan;
            
            // Calculate current period dates
            $periodStart = $subscription->starts_at;
            $periodEnd = $subscription->ends_at;
            
            // If no end date, calculate based on billing cycle
            if (!$periodEnd) {
                if ($subscription->billing_cycle === 'yearly') {
                    $periodEnd = $periodStart->copy()->addYear();
                } else {
                    $periodEnd = $periodStart->copy()->addMonth();
                }
            }
            
            // Calculate next billing date (only for paid plans)
            $nextBillingDate = null;
            if ($plan->name !== 'free' && $subscription->status === 'active') {
                $nextBillingDate = $periodEnd;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Current subscription retrieved successfully',
                'data' => [
                    'id' => (string) $subscription->id,
                    'plan_id' => (string) $subscription->subscription_plan_id,
                    'plan_slug' => $plan->name, // 'free' or 'pro'
                    'status' => $subscription->status,
                    'billing_cycle' => $subscription->billing_cycle,
                    'start_date' => $subscription->starts_at->toIso8601String(),
                    'end_date' => $subscription->ends_at?->toIso8601String(),
                    'next_billing_date' => $nextBillingDate?->toIso8601String(),
                    'cancelled_at' => $subscription->cancelled_at?->toIso8601String(),
                    'current_period_start' => $periodStart->timestamp,
                    'current_period_end' => $periodEnd->timestamp,
                    'is_active' => $subscription->status === 'active',
                    'is_cancelled' => $subscription->status === 'cancelled',
                    'will_renew' => $subscription->status === 'active' && $plan->name === 'pro',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving subscription. Please try again later.',
            ], 500);
        }
    }

    public function subscribe(Request $request)
    {
        try {
            $validated = $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
                'billing_cycle' => 'required|in:monthly,yearly',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login again.',
                ], 401);
            }

            $organization = $user->organization();

            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization not found.',
                ], 404);
            }

            // Check Razorpay configuration
            $razorpayKey = config('services.razorpay.key');
            $razorpaySecret = config('services.razorpay.secret');
            
            if (!$razorpayKey || !$razorpaySecret) {
                Log::error('Razorpay configuration missing', [
                    'organization_id' => $organization->id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway configuration is missing. Please contact support.',
                ], 500);
            }

            // Ensure organization has required fields for Razorpay customer
            $email = $organization->email ?? $user->email;
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required for subscription. Please update your organization or profile email.',
                ], 400);
            }

            // Create Razorpay subscription
            try {
                $razorpaySubscription = $this->razorpayService->createSubscription(
                    $organization,
                    $validated['plan_id'],
                    $validated['billing_cycle']
                );
            } catch (\Razorpay\Api\Errors\BadRequestError $e) {
                Log::error('Razorpay BadRequestError', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment request: ' . $e->getMessage(),
                ], 400);
            } catch (RazorpayError $e) {
                Log::error('Razorpay API Error', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway error: ' . $e->getMessage(),
                ], 500);
            } catch (\Exception $e) {
                Log::error('Error creating Razorpay subscription', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating subscription. Please try again later.',
                ], 500);
            }

            // Cancel any existing trial subscription before creating paid subscription
            $existingTrial = Subscription::where('organization_id', $organization->id)
                ->where('status', 'trial')
                ->whereNull('razorpay_subscription_id') // Only cancel free trials, not paid ones
                ->first();
            
            if ($existingTrial) {
                $existingTrial->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
                Log::info('Cancelled existing trial subscription before creating paid subscription', [
                    'trial_subscription_id' => $existingTrial->id,
                    'organization_id' => $organization->id,
                ]);
            }

            // Create subscription record
            $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
            $subscription = Subscription::create([
                'organization_id' => $organization->id,
                'subscription_plan_id' => $validated['plan_id'],
                'billing_cycle' => $validated['billing_cycle'],
                'status' => 'trial',
                'starts_at' => now(),
                'razorpay_subscription_id' => $razorpaySubscription->id,
                'razorpay_customer_id' => $organization->razorpay_customer_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'data' => [
                    'razorpay_subscription_id' => $razorpaySubscription->id,
                    'razorpay_key' => $razorpayKey,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in subscribe endpoint', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }

    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $currentSubscription = $this->subscriptionService->getCurrentSubscription($organization);
        $newPlan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // Cancel current subscription if it's paid
        if ($currentSubscription->razorpay_subscription_id) {
            // Cancel via Razorpay
            try {
                $this->razorpayService->razorpay->subscription->fetch($currentSubscription->razorpay_subscription_id)->cancel();
            } catch (\Exception $e) {
                // Log error but continue
            }
        }

        // Create new subscription
        $razorpaySubscription = $this->razorpayService->createSubscription(
            $organization,
            $validated['plan_id'],
            $currentSubscription->billing_cycle
        );

        // Update current subscription
        $currentSubscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Create new subscription
        $newSubscription = Subscription::create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $validated['plan_id'],
            'billing_cycle' => $currentSubscription->billing_cycle,
            'status' => 'trial',
            'starts_at' => now(),
            'razorpay_subscription_id' => $razorpaySubscription->id,
            'razorpay_customer_id' => $organization->razorpay_customer_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription upgraded successfully.',
            'data' => [
                'subscription_id' => $newSubscription->id,
            ]
        ]);
    }

    public function cancel(Request $request)
    {
        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $subscription = $this->subscriptionService->getCurrentSubscription($organization);

        if ($subscription->razorpay_subscription_id) {
            try {
                $this->razorpayService->razorpay->subscription->fetch($subscription->razorpay_subscription_id)->cancel();
            } catch (\Exception $e) {
                // Log error but continue
            }
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully.',
        ]);
    }

    public function resume(Request $request)
    {
        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $subscription = Subscription::where('organization_id', $organization->id)
            ->where('status', 'cancelled')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No cancelled subscription found.',
            ], 404);
        }

        if ($subscription->razorpay_subscription_id) {
            try {
                $this->razorpayService->razorpay->subscription->fetch($subscription->razorpay_subscription_id)->resume();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to resume subscription: ' . $e->getMessage(),
                ], 400);
            }
        }

        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription resumed successfully.',
        ]);
    }

    public function changeBilling(Request $request)
    {
        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $subscription = $this->subscriptionService->getCurrentSubscription($organization);

        // Cancel current and create new with different billing cycle
        if ($subscription->razorpay_subscription_id) {
            try {
                $this->razorpayService->razorpay->subscription->fetch($subscription->razorpay_subscription_id)->cancel();
            } catch (\Exception $e) {
                // Log error but continue
            }
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Create new subscription with new billing cycle
        $razorpaySubscription = $this->razorpayService->createSubscription(
            $organization,
            $subscription->subscription_plan_id,
            $validated['billing_cycle']
        );

        $newSubscription = Subscription::create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'billing_cycle' => $validated['billing_cycle'],
            'status' => 'trial',
            'starts_at' => now(),
            'razorpay_subscription_id' => $razorpaySubscription->id,
            'razorpay_customer_id' => $organization->razorpay_customer_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Billing cycle changed successfully.',
            'data' => [
                'razorpay_subscription_id' => $razorpaySubscription->id,
                'razorpay_key' => config('services.razorpay.key'),
            ]
        ]);
    }

    public function usage(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login again.',
                ], 401);
            }
            
            $organization = $user->organization();
            
            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization not found.',
                ], 404);
            }

            // Get current subscription with error handling
            try {
                $subscription = $this->subscriptionService->getCurrentSubscription($organization);
            } catch (\Exception $e) {
                Log::error('Error getting subscription', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while retrieving subscription. Please try again later.',
                ], 500);
            }
            
            // Ensure subscription has a plan
            if (!$subscription) {
                Log::error('Subscription is null', ['organization_id' => $organization->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found. Please contact support.',
                ], 404);
            }
            
            // Load plan relationship if not already loaded
            try {
                if (!$subscription->relationLoaded('plan')) {
                    $subscription->load('plan');
                }
                
                $plan = $subscription->plan;
            } catch (\Exception $e) {
                Log::error('Error loading plan relationship', [
                    'subscription_id' => $subscription->id,
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while retrieving subscription plan. Please try again later.',
                ], 500);
            }
            
            if (!$plan) {
                Log::error('Plan not found for subscription', [
                    'subscription_id' => $subscription->id,
                    'subscription_plan_id' => $subscription->subscription_plan_id,
                    'organization_id' => $organization->id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription plan not found. Please contact support.',
                ], 500);
            }
            
            // Safely decode limits JSON
            $limits = $plan->limits ?? [];
            if (is_string($limits)) {
                $decoded = json_decode($limits, true);
                $limits = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
            }
            if (!is_array($limits)) {
                $limits = [];
            }
            
            // Calculate current month period
            $periodStart = now()->startOfMonth();
            
            // Get usage counts with error handling
            try {
                $meetingsCount = $organization->meetings()
                    ->where('created_at', '>=', $periodStart)
                    ->count();
            } catch (\Exception $e) {
                Log::warning('Error counting meetings', ['error' => $e->getMessage()]);
                $meetingsCount = 0;
            }
            
            try {
                $contactsCount = $organization->contacts()->count();
            } catch (\Exception $e) {
                Log::warning('Error counting contacts', ['error' => $e->getMessage()]);
                $contactsCount = 0;
            }
            
            try {
                $activeSurveysCount = $organization->surveys()
                    ->whereRaw('LOWER(status) = ?', ['active'])
                    ->count();
            } catch (\Exception $e) {
                Log::warning('Error counting active surveys', ['error' => $e->getMessage()]);
                $activeSurveysCount = 0;
            }
            
            // Get survey responses count (from survey_responses and survey_submissions)
            $surveyResponsesCount = $this->subscriptionService->getSurveyResponsesCount($organization, $periodStart);
            
            try {
                $usersCount = $organization->users()->count();
            } catch (\Exception $e) {
                Log::warning('Error counting users', ['error' => $e->getMessage()]);
                $usersCount = 0;
            }
            
            // Calculate storage in bytes from survey_attachments
            try {
                $storageUsedBytes = (int) ($organization->surveyAttachments()->sum('size') ?? 0);
            } catch (\Exception $e) {
                Log::warning('Error calculating storage', ['error' => $e->getMessage()]);
                $storageUsedBytes = 0;
            }
            
            // Format limits from plan (handle null values)
            $meetingsLimit = $limits['meetings_per_month'] ?? -1;
            $contactsLimit = $limits['contacts'] ?? -1;
            $activeSurveysLimit = $limits['active_surveys'] ?? -1;
            $surveyResponsesLimit = $limits['survey_responses_per_month'] ?? -1;
            $usersLimit = $limits['users'] ?? -1;
            
            // Convert storage_mb to bytes if needed
            $storageLimitBytes = isset($limits['storage_mb']) 
                ? ($limits['storage_mb'] === -1 ? -1 : (int) $limits['storage_mb'] * 1024 * 1024)
                : ($limits['storage_bytes'] ?? 1073741824);
            
            return response()->json([
                'success' => true,
                'message' => 'Usage statistics retrieved successfully',
                'data' => [
                    'meetings_count' => (int) $meetingsCount,
                    'meetings_limit' => (int) $meetingsLimit,
                    'contacts_count' => (int) $contactsCount,
                    'contacts_limit' => (int) $contactsLimit,
                    'active_surveys_count' => (int) $activeSurveysCount,
                    'active_surveys_limit' => (int) $activeSurveysLimit,
                    'survey_responses_count' => (int) $surveyResponsesCount,
                    'survey_responses_limit' => (int) $surveyResponsesLimit,
                    'users_count' => (int) $usersCount,
                    'users_limit' => (int) $usersLimit,
                    'storage_used_bytes' => (int) $storageUsedBytes,
                    'storage_limit_bytes' => (int) $storageLimitBytes,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in usage endpoint', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving usage statistics. Please try again later.',
            ], 500);
        }
    }

    public function limits(Request $request)
    {
        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $subscription = $this->subscriptionService->getCurrentSubscription($organization);

        return response()->json([
            'success' => true,
            'data' => [
                'limits' => $subscription->plan->limits,
                'features' => $subscription->plan->features,
            ]
        ]);
    }

    public function checkLimit(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string',
            'count' => 'integer|min:1',
        ]);

        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $result = $this->subscriptionService->checkLimit(
            $organization,
            $validated['action'],
            $validated['count'] ?? 1
        );

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function addons(Request $request)
    {
        $addons = SubscriptionAddOn::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $addons
        ]);
    }

    public function purchaseAddon(Request $request)
    {
        $validated = $request->validate([
            'addon_id' => 'required|exists:subscription_add_ons,id',
            'quantity' => 'integer|min:1',
        ]);

        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $addon = SubscriptionAddOn::findOrFail($validated['addon_id']);
        $quantity = $validated['quantity'] ?? 1;

        // Check if addon already exists
        $existingAddon = OrganizationAddOn::where('organization_id', $organization->id)
            ->where('subscription_add_on_id', $addon->id)
            ->where('status', 'active')
            ->first();

        if ($existingAddon) {
            $existingAddon->update([
                'quantity' => $quantity,
            ]);
        } else {
            OrganizationAddOn::create([
                'organization_id' => $organization->id,
                'subscription_add_on_id' => $addon->id,
                'quantity' => $quantity,
                'status' => 'active',
                'starts_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Add-on purchased successfully.',
        ]);
    }

    public function cancelAddon(Request $request)
    {
        $validated = $request->validate([
            'addon_id' => 'required|exists:organization_add_ons,id',
        ]);

        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $addon = OrganizationAddOn::where('id', $validated['addon_id'])
            ->where('organization_id', $organization->id)
            ->first();

        if (!$addon) {
            return response()->json([
                'success' => false,
                'message' => 'Add-on not found.',
            ], 404);
        }

        $addon->update([
            'status' => 'cancelled',
            'ends_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Add-on cancelled successfully.',
        ]);
    }

    public function activeAddons(Request $request)
    {
        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $addons = OrganizationAddOn::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->with('addOn')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addons
        ]);
    }

    public function invoices(Request $request)
    {
        $organization = $request->user()->organization();

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.',
            ], 404);
        }

        $transactions = Transaction::where('organization_id', $organization->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function downloadInvoice($id)
    {
        $transaction = Transaction::findOrFail($id);

        // TODO: Generate PDF invoice
        // For now, return transaction data
        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    public function verifyPayment(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $paymentId = $request->input('payment_id', '');
            $signature = $request->input('signature', '');
            
            // Check if this is a subscription (starts with "sub_")
            $isSubscription = !empty($orderId) && strpos($orderId, 'sub_') === 0;
            
            // Validation rules - different for subscriptions vs one-time payments
            $rules = [
                'order_id' => 'required|string',
            ];
            
            if ($isSubscription) {
                // For subscriptions, payment_id and signature are optional
                // Backend will fetch them from Razorpay subscription API
                $rules['payment_id'] = 'nullable|string';
                $rules['signature'] = 'nullable|string';
            } else {
                // For one-time payments, require all fields
                $rules['payment_id'] = 'required|string';
                $rules['signature'] = 'required|string';
            }
            
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment data provided',
                    'errors' => $validator->errors(),
                    'data' => null,
                ], 400);
            }

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login again.',
                    'data' => null,
                ], 401);
            }

            $organization = $user->organization();
            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization not found.',
                    'data' => null,
                ], 404);
            }

            // Route to appropriate handler
            if ($isSubscription) {
                return $this->verifySubscriptionPayment($organization, $orderId);
            } else {
                return $this->verifyOneTimePayment($organization, $orderId, $paymentId, $signature);
            }


        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment data provided',
                'errors' => $e->errors(),
                'data' => null,
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error verifying payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying payment',
                'data' => null,
            ], 500);
        }
    }

    /**
     * Verify subscription payment
     * For subscriptions, Razorpay doesn't provide payment_id and signature in callback
     * We need to fetch subscription details from Razorpay API
     */
    private function verifySubscriptionPayment($organization, $subscriptionId)
    {
        try {
            // Find subscription record in database
            $subscription = Subscription::where('organization_id', $organization->id)
                ->where('razorpay_subscription_id', $subscriptionId)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found or does not belong to this organization',
                    'data' => null,
                ], 404);
            }

            // Fetch subscription details from Razorpay
            try {
                $razorpaySubscription = $this->razorpayService->razorpay->subscription->fetch($subscriptionId);
            } catch (RazorpayError $e) {
                Log::error('Razorpay API Error in subscription verification', [
                    'subscription_id' => $subscriptionId,
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify subscription with Razorpay. Please try again later.',
                    'data' => null,
                ], 500);
            } catch (\Exception $e) {
                Log::error('Failed to fetch subscription from Razorpay', [
                    'subscription_id' => $subscriptionId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch subscription details from Razorpay',
                    'data' => null,
                ], 500);
            }

            // Check subscription status
            // Status can be: 'created', 'authenticated', 'active', 'pending', 'halted', 'cancelled', 'completed', 'expired'
            // 'created' - Subscription created, payment processing
            // 'authenticated' - Payment authenticated, subscription will be activated
            // 'active' - Subscription is active and payment successful
            $status = strtolower($razorpaySubscription->status ?? '');
            
            // Accept 'created', 'authenticated', and 'active' statuses
            // 'created' means subscription exists and payment is being processed
            // Webhooks will update status to 'active' when payment completes
            if (!in_array($status, ['active', 'authenticated', 'created'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription is not in a valid state. Current status: ' . ($razorpaySubscription->status ?? 'unknown'),
                    'data' => null,
                ], 400);
            }
            
            // Log if status is 'created' (payment still processing)
            if ($status === 'created') {
                Log::info('Subscription verification with created status - payment may still be processing', [
                    'subscription_id' => $subscriptionId,
                    'organization_id' => $organization->id,
                ]);
            }

            // For subscriptions, payment details are not immediately available
            // The subscription status being 'active' or 'authenticated' is proof of successful payment
            // Payment details will be available through webhooks or can be fetched later
            $paymentId = null;
            $amount = 0;
            
            // Try to get payment info from subscription object if available
            // Some subscription objects may have payment_id in the response
            if (isset($razorpaySubscription->payment_id) && !empty($razorpaySubscription->payment_id)) {
                $paymentId = $razorpaySubscription->payment_id;
            }
            
            // Try to get amount from subscription if available
            if (isset($razorpaySubscription->plan_amount) && !empty($razorpaySubscription->plan_amount)) {
                $amount = $razorpaySubscription->plan_amount;
            }
            
            // If payment ID is not available, that's okay - subscription status is sufficient
            // Payment details will come through webhooks
            if (!$paymentId) {
                Log::info('Payment ID not available in subscription response - will be available via webhook', [
                    'subscription_id' => $subscriptionId,
                    'subscription_status' => $razorpaySubscription->status,
                ]);
            }

            // Calculate period end based on billing cycle
            $periodStart = now();
            $periodEnd = $subscription->billing_cycle === 'yearly'
                ? $periodStart->copy()->addYear()
                : $periodStart->copy()->addMonth();

            // Update subscription status
            $subscription->update([
                'status' => 'active',
                'starts_at' => $periodStart,
                'ends_at' => $periodEnd,
            ]);

            // Create or update transaction record if payment ID is available
            if ($paymentId) {
                Transaction::updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'subscription_id' => $subscription->id,
                        'razorpay_payment_id' => $paymentId,
                    ],
                    [
                        'razorpay_order_id' => $subscriptionId,
                        'razorpay_signature' => null, // Not available for subscriptions
                        'amount' => $amount,
                        'currency' => 'INR',
                        'status' => 'completed',
                        'type' => 'subscription',
                        'description' => 'Subscription payment',
                    ]
                );
            }

            $plan = $subscription->plan;

            // Calculate next billing date (only for paid plans)
            $nextBillingDate = null;
            if ($plan->name !== 'free' && $subscription->status === 'active') {
                $nextBillingDate = $periodEnd;
            }

            // Format subscription response
            $subscriptionData = [
                'id' => (string) $subscription->id,
                'plan_id' => (string) $subscription->subscription_plan_id,
                'plan_slug' => $plan->name, // 'free' or 'pro'
                'status' => $subscription->status,
                'billing_cycle' => $subscription->billing_cycle,
                'start_date' => $subscription->starts_at->toIso8601String(),
                'end_date' => $subscription->ends_at?->toIso8601String(),
                'next_billing_date' => $nextBillingDate?->toIso8601String(),
                'cancelled_at' => $subscription->cancelled_at?->toIso8601String(),
                'current_period_start' => $periodStart->timestamp,
                'current_period_end' => $periodEnd->timestamp,
                'is_active' => $subscription->status === 'active',
                'is_cancelled' => $subscription->status === 'cancelled' || $subscription->cancelled_at !== null,
                'will_renew' => $subscription->status === 'active' && $plan->name !== 'free' && $subscription->cancelled_at === null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => $subscriptionData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Subscription verification failed', [
                'subscription_id' => $subscriptionId,
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify subscription: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Verify one-time payment
     * For one-time payments, all fields (payment_id, signature) are required
     */
    private function verifyOneTimePayment($organization, $orderId, $paymentId, $signature)
    {
        try {
            // Verify payment signature with Razorpay
            $isVerified = $this->razorpayService->verifyPaymentSignature(
                $orderId,
                $paymentId,
                $signature
            );

            if (!$isVerified) {
                Log::error('Payment verification failed', [
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed. Invalid signature.',
                    'data' => null,
                ], 400);
            }

            // Find subscription by razorpay_subscription_id or order_id
            $subscription = Subscription::where('organization_id', $organization->id)
                ->where('razorpay_subscription_id', $orderId)
                ->first();

            // If not found, check if order_id is stored in transactions table
            if (!$subscription) {
                $transaction = Transaction::where('organization_id', $organization->id)
                    ->where(function ($query) use ($orderId, $paymentId) {
                        $query->where('razorpay_order_id', $orderId)
                              ->orWhere('razorpay_payment_id', $paymentId);
                    })
                    ->first();

                if ($transaction && $transaction->subscription_id) {
                    $subscription = Subscription::find($transaction->subscription_id);
                }
            }

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or does not belong to this user',
                    'data' => null,
                ], 404);
            }

            // Fetch payment details from Razorpay to get amount
            try {
                $payment = $this->razorpayService->razorpay->payment->fetch($paymentId);
                $amount = $payment->amount ?? 0;
            } catch (\Exception $e) {
                Log::warning('Could not fetch payment details from Razorpay', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
                $amount = 0;
            }

            // Create or update transaction record
            Transaction::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'subscription_id' => $subscription->id,
                    'razorpay_payment_id' => $paymentId,
                ],
                [
                    'razorpay_order_id' => $orderId,
                    'razorpay_signature' => $signature,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'status' => 'completed',
                    'type' => 'subscription',
                    'description' => 'Subscription payment',
                ]
            );

            // Calculate period end based on billing cycle
            $periodStart = now();
            $periodEnd = $subscription->billing_cycle === 'yearly'
                ? $periodStart->copy()->addYear()
                : $periodStart->copy()->addMonth();

            // Update subscription status
            $subscription->update([
                'status' => 'active',
                'starts_at' => $periodStart,
                'ends_at' => $periodEnd,
            ]);

            $plan = $subscription->plan;

            // Calculate next billing date (only for paid plans)
            $nextBillingDate = null;
            if ($plan->name !== 'free' && $subscription->status === 'active') {
                $nextBillingDate = $periodEnd;
            }

            // Format subscription response
            $subscriptionData = [
                'id' => (string) $subscription->id,
                'plan_id' => (string) $subscription->subscription_plan_id,
                'plan_slug' => $plan->name, // 'free' or 'pro'
                'status' => $subscription->status,
                'billing_cycle' => $subscription->billing_cycle,
                'start_date' => $subscription->starts_at->toIso8601String(),
                'end_date' => $subscription->ends_at?->toIso8601String(),
                'next_billing_date' => $nextBillingDate?->toIso8601String(),
                'cancelled_at' => $subscription->cancelled_at?->toIso8601String(),
                'current_period_start' => $periodStart->timestamp,
                'current_period_end' => $periodEnd->timestamp,
                'is_active' => $subscription->status === 'active',
                'is_cancelled' => $subscription->status === 'cancelled' || $subscription->cancelled_at !== null,
                'will_renew' => $subscription->status === 'active' && $plan->name !== 'free' && $subscription->cancelled_at === null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => $subscriptionData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('One-time payment verification failed', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying payment',
                'data' => null,
            ], 500);
        }
    }
}
