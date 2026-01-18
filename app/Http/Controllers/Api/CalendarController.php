<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use App\Services\SubscriptionService;
use App\Models\Meeting;
use App\Traits\AppliesMeetingFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendarController extends Controller
{
    use AppliesMeetingFilters;

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Return month calendar data. Accepts optional year and month.
     */
    public function currentMonth(Request $request)
    {
        $user = $request->user();

        // Validate optional inputs; default to current year/month
        $validated = $request->validate([
            'year'  => ['nullable', 'integer', 'min:1970', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            
            'filters' => 'nullable|array',
            'filters.meeting_type' => 'nullable|string|in:Video Call,In-Person Meeting,Phone Call,Online Meeting',
            'filters.attendees' => 'nullable|string|in:1-on-1,small,medium,large',
            'filters.duration' => 'nullable|string|in:15,30,60,120',
            'filters.status' => 'nullable|string|in:upcoming,completed,cancelled',
        ]);

        $year = (int)($validated['year'] ?? now()->year);
        $month = (int)($validated['month'] ?? now()->month);
        $filters = $request->input('filters', []);

        $organizationIds = $user->organizations()->pluck('organizations.id')->all();
        $target = Carbon::createFromDate($year, $month, 1);

        // Create query modifier closure for filters
        $queryModifier = null;
        if (!empty($filters)) {
            $queryModifier = function ($query) use ($filters) {
                return $this->applyMeetingFilters($query, $filters);
            };
        }

        if (empty($organizationIds)) {
            return response()->json([
                'data' => CalendarService::buildMonth([], $target, $queryModifier),
                'message' => 'No organization found. Returning empty month.',
            ]);
        }

        $data = CalendarService::buildMonth($organizationIds, $target, $queryModifier);
        $data['filters_applied'] = $filters;

        return response()->json([
            'data' => $data,
            'message' => 'Month retrieved successfully.',
        ]);
    }

    /**
     * Return current week's calendar grid (Mon–Sun). Optional date or Y/M/D.
     */
    public function currentWeek(Request $request)
    {
        $user = $request->user();
        $organization = $user->organization();

        if ($organization) {
            try {
                $subscription = $this->subscriptionService->getCurrentSubscription($organization);
                
                // Load plan relationship if not loaded
                if (!$subscription->relationLoaded('plan')) {
                    $subscription->load('plan');
                }
                
                // Check if plan exists
                if (!$subscription->plan) {
                    Log::error('Plan not found for subscription', [
                        'subscription_id' => $subscription->id,
                        'organization_id' => $organization->id,
                    ]);
                    // Continue without subscription check if plan is missing
                } else {
                    $features = $subscription->plan->features ?? [];
                    
                    if (!in_array('week', $features['calendar_views'] ?? [])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Week view is a Pro feature.',
                            'data' => ['upgrade_required' => true]
                        ], 403);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error checking subscription for week view', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continue without subscription check if error occurs
            }
        }

        $validated = $request->validate([
            'date'  => ['nullable', 'date'],
            'year'  => ['nullable', 'integer', 'min:1970', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'day'   => ['nullable', 'integer', 'min:1', 'max:31'],
            'filters' => 'nullable|array',
            'filters.meeting_type' => 'nullable|string|in:Video Call,In-Person Meeting,Phone Call,Online Meeting',
            'filters.attendees' => 'nullable|string|in:1-on-1,small,medium,large',
            'filters.duration' => 'nullable|string|in:15,30,60,120',
            'filters.status' => 'nullable|string|in:upcoming,completed,cancelled',
        ]);

        if (!empty($validated['date'])) {
            $target = Carbon::parse($validated['date']);
        } else {
            $year = (int)($validated['year'] ?? now()->year);
            
            $month = (int)($validated['month'] ?? now()->month);
            $day = (int)($validated['day'] ?? now()->day);
            $target = Carbon::createFromDate($year, $month, $day);
        }

        $filters = $request->input('filters', []);
        $organizationIds = $user->organizations()->pluck('organizations.id')->all();

        // Create query modifier closure for filters
        $queryModifier = null;
        if (!empty($filters)) {
            $queryModifier = function ($query) use ($filters) {
                return $this->applyMeetingFilters($query, $filters);
            };
        }

        if (empty($organizationIds)) {
            return response()->json([
                'data' => CalendarService::buildWeek([], $target, $queryModifier),
                'message' => 'No organization found. Returning empty week.',
            ]);
        }

        $data = CalendarService::buildWeek($organizationIds, $target, $queryModifier);
        $data['filters_applied'] = $filters;

        return response()->json([
            'data' => $data,
            'message' => 'Week retrieved successfully.',
        ]);
    }

    /**
     * Return current day's meetings. Optional date or Y/M/D.
     */
    public function currentDay(Request $request)
    {
        $user = $request->user();
        $organization = $user->organization();

        if ($organization) {
            try {
                $subscription = $this->subscriptionService->getCurrentSubscription($organization);
                
                // Load plan relationship if not loaded
                if (!$subscription->relationLoaded('plan')) {
                    $subscription->load('plan');
                }
                
                // Check if plan exists
                if (!$subscription->plan) {
                    Log::error('Plan not found for subscription', [
                        'subscription_id' => $subscription->id,
                        'organization_id' => $organization->id,
                    ]);
                    // Continue without subscription check if plan is missing
                } else {
                    $features = $subscription->plan->features ?? [];
                    
                    if (!in_array('day', $features['calendar_views'] ?? [])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Day view is a Pro feature.',
                            'data' => ['upgrade_required' => true]
                        ], 403);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error checking subscription for day view', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continue without subscription check if error occurs
            }
        }

        $validated = $request->validate([
            'date'  => ['nullable', 'date'],
            'year'  => ['nullable', 'integer', 'min:1970', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'day'   => ['nullable', 'integer', 'min:1', 'max:31'],
            'filters' => 'nullable|array',
            'filters.meeting_type' => 'nullable|string|in:Video Call,In-Person Meeting,Phone Call,Online Meeting',
            'filters.attendees' => 'nullable|string|in:1-on-1,small,medium,large',
            'filters.duration' => 'nullable|string|in:15,30,60,120',
            'filters.status' => 'nullable|string|in:upcoming,completed,cancelled',
        ]);

        if (!empty($validated['date'])) {
            $target = Carbon::parse($validated['date']);
        } else {
            $year = (int)($validated['year'] ?? now()->year);
            $month = (int)($validated['month'] ?? now()->month);
            $day = (int)($validated['day'] ?? now()->day);
            $target = Carbon::createFromDate($year, $month, $day);
        }

        $filters = $request->input('filters', []);
        $organizationIds = $user->organizations()->pluck('organizations.id')->all();

        // Create query modifier closure for filters
        $queryModifier = null;
        if (!empty($filters)) {
            $queryModifier = function ($query) use ($filters) {
                return $this->applyMeetingFilters($query, $filters);
            };
        }

        if (empty($organizationIds)) {
            return response()->json([
                'data' => CalendarService::buildDay([], $target, $queryModifier),
                'message' => 'No organization found. Returning empty day.',
            ]);
        }

        $data = CalendarService::buildDay($organizationIds, $target, $queryModifier);
        $data['filters_applied'] = $filters;

        return response()->json([
            'data' => $data,
            'message' => 'Day retrieved successfully.',
        ]);
    }

    /**
     * Get calendar statistics (Total Meetings, This Week, Today)
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        // Get organization IDs from user's organizations
        $organizationIds = $user->organizations()->pluck('organizations.id')->all();

        if (empty($organizationIds)) {
            return response()->json([
                'data' => [
                    'total_meetings' => 0,
                    'this_week' => 0,
                    'today' => 0,
                ],
                'message' => 'Statistics retrieved successfully.',
            ]);
        }

        $now = now();
        $startOfWeek = $now->copy()->startOfWeek(); // Monday
        $endOfWeek = $now->copy()->endOfWeek(); // Sunday
        $todayDate = $now->toDateString();

        // Total meetings count
        $totalMeetings = Meeting::whereIn('organization_id', $organizationIds)->count();

        // Meetings scheduled this week
        $thisWeek = Meeting::whereIn('organization_id', $organizationIds)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->count();

        // Meetings scheduled today
        $todayCount = Meeting::whereIn('organization_id', $organizationIds)
            ->whereDate('date', $todayDate)
            ->count();

        return response()->json([
            'data' => [
                'total_meetings' => $totalMeetings,
                'this_week' => $thisWeek,
                'today' => $todayCount,
            ],
            'message' => 'Statistics retrieved successfully.',
        ]);
    }
}


