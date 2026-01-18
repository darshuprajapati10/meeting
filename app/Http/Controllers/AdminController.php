<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Organization;
use App\Models\Contact;
use App\Models\Meeting;
use App\Models\Survey;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'total_organizations' => Organization::count(),
            'total_contacts' => Contact::count(),
            'total_meetings' => Meeting::count(),
            'total_surveys' => Survey::count(),
            'platform_admins' => User::where('is_platform_admin', true)->count(),
        ];

        // Get recent users (last 10)
        $recentUsers = User::latest()
            ->take(10)
            ->select('id', 'name', 'email', 'is_platform_admin', 'created_at')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_platform_admin' => $user->is_platform_admin ?? false,
                    'created_at' => $user->created_at->toIso8601String(),
                ];
            });

        // Get recent organizations (last 10)
        $recentOrganizations = Organization::latest()
            ->take(10)
            ->select('id', 'name', 'created_at')
            ->get()
            ->map(function ($org) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'created_at' => $org->created_at->toIso8601String(),
                ];
            });

        // Get users by month for the last 6 months
        $usersByMonthData = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Always return 6 months, even if no data
        $usersByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i)->startOfMonth();
            $monthKey = $date->format('Y-m');
            $monthName = $date->format('M Y');
            
            $usersByMonth[] = [
                'month' => $monthName,
                'monthKey' => $monthKey,
                'count' => $usersByMonthData[$monthKey] ?? 0,
            ];
        }

        // Get subscription plans with users
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($plan) {
                // Get organizations with this plan (active subscriptions)
                $organizations = Organization::whereHas('subscription', function ($query) use ($plan) {
                    $query->where('subscription_plan_id', $plan->id)
                        ->whereRaw('LOWER(status) = ?', ['active']);
                })->get();

                // Get all users from these organizations
                $users = collect();
                foreach ($organizations as $org) {
                    $orgUsers = $org->users()
                        ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'organization_users.role')
                        ->get()
                        ->map(function ($user) use ($org) {
                            return [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'organization_id' => $org->id,
                                'organization_name' => $org->name,
                                'role' => $user->pivot->role ?? 'member',
                                'created_at' => $user->created_at->toIso8601String(),
                            ];
                        });
                    $users = $users->merge($orgUsers);
                }

                // If no subscriptions exist and it's free plan, check organizations without subscriptions (default to free)
                if ($plan->name === 'free' && $organizations->isEmpty()) {
                    $organizationsWithoutSub = Organization::whereDoesntHave('subscription')->get();
                    foreach ($organizationsWithoutSub as $org) {
                        $orgUsers = $org->users()
                            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'organization_users.role')
                            ->get()
                            ->map(function ($user) use ($org) {
                                return [
                                    'id' => $user->id,
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'organization_id' => $org->id,
                                    'organization_name' => $org->name,
                                    'role' => $user->pivot->role ?? 'member',
                                    'created_at' => $user->created_at->toIso8601String(),
                                ];
                            });
                        $users = $users->merge($orgUsers);
                    }
                }

                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'display_name' => $plan->display_name,
                    'description' => $plan->description,
                    'price_monthly' => $plan->price_monthly,
                    'price_yearly' => $plan->price_yearly,
                    'price_monthly_formatted' => '₹' . ($plan->price_monthly / 100),
                    'price_yearly_formatted' => '₹' . ($plan->price_yearly / 100),
                    'is_active' => $plan->is_active,
                    'sort_order' => $plan->sort_order,
                    'user_count' => $users->count(),
                    'organization_count' => $plan->name === 'free' 
                        ? ($organizations->count() + Organization::whereDoesntHave('subscription')->count())
                        : $organizations->count(),
                    'users' => $users->take(10)->values(), // Show first 10 users per plan
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentOrganizations' => $recentOrganizations,
            'usersByMonth' => $usersByMonth,
            'subscriptionPlans' => $subscriptionPlans,
        ]);
    }

    /**
     * Display all users.
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $users = $query->latest()
            ->select('id', 'name', 'email', 'is_platform_admin', 'created_at')
            ->paginate($perPage)
            ->withQueryString();

        $usersData = $users->map(function ($user) {
            $user->load('organizations');
            
            // Get primary plan from first organization
            $primaryPlan = 'free';
            $primaryPlanDisplay = 'Free';
            $isTrial = false;
            $trialDaysRemaining = null;
            
            if ($user->organizations->isNotEmpty()) {
                try {
                    $firstOrg = $user->organizations->first();
                    $subscription = $this->subscriptionService->getCurrentSubscription($firstOrg);
                    
                    if ($subscription && $subscription->plan) {
                        $primaryPlan = $subscription->plan->name;
                        $primaryPlanDisplay = $subscription->plan->display_name ?? ucfirst($subscription->plan->name);
                        
                        // Check if it's a trial subscription
                        if ($subscription->status === 'trial' && $subscription->trial_ends_at) {
                            $isTrial = true;
                            $now = now();
                            $trialEnd = $subscription->trial_ends_at;
                            
                            if ($trialEnd->isFuture()) {
                                $trialDaysRemaining = $now->diffInDays($trialEnd, false);
                            } else {
                                $trialDaysRemaining = 0; // Trial expired
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Keep default Free plan on error
                }
            }
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_platform_admin' => $user->is_platform_admin ?? false,
                'created_at' => $user->created_at->toIso8601String(),
                'plan_name' => $primaryPlan,
                'plan_display_name' => $primaryPlanDisplay,
                'is_trial' => $isTrial,
                'trial_days_remaining' => $trialDaysRemaining,
                'organizations' => $user->organizations->map(function ($org) {
                    $orgPlan = 'Free';
                    $orgPlanDisplay = 'Free';
                    
                    try {
                        $subscription = $this->subscriptionService->getCurrentSubscription($org);
                        if ($subscription && $subscription->plan) {
                            $orgPlan = $subscription->plan->name;
                            $orgPlanDisplay = $subscription->plan->display_name ?? ucfirst($subscription->plan->name);
                        }
                    } catch (\Exception $e) {
                        // Keep default Free plan on error
                    }
                    
                    return [
                        'id' => $org->id,
                        'name' => $org->name,
                        'role' => $org->pivot->role ?? 'member',
                        'plan_name' => $orgPlan,
                        'plan_display_name' => $orgPlanDisplay,
                    ];
                }),
            ];
        });

        return Inertia::render('Admin/Users', [
            'users' => $usersData,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Display all organizations.
     */
    public function organizations(Request $request)
    {
        $query = Organization::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $organizations = $query->latest()
            ->select('id', 'name', 'email', 'type', 'status', 'created_at')
            ->paginate($perPage)
            ->withQueryString();

        $organizationsData = $organizations->map(function ($org) {
            $org->load('subscription.plan', 'users');
            $subscription = $org->subscription;
            return [
                'id' => $org->id,
                'name' => $org->name,
                'email' => $org->email,
                'type' => $org->type,
                'status' => $org->status,
                'created_at' => $org->created_at->toIso8601String(),
                'user_count' => $org->users->count(),
                'subscription_plan' => $subscription && $subscription->plan 
                    ? [
                        'id' => $subscription->plan->id,
                        'name' => $subscription->plan->name,
                        'display_name' => $subscription->plan->display_name,
                        'status' => $subscription->status,
                    ]
                    : null,
            ];
        });

        return Inertia::render('Admin/Organizations', [
            'organizations' => $organizationsData,
            'pagination' => [
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'per_page' => $organizations->perPage(),
                'total' => $organizations->total(),
                'from' => $organizations->firstItem(),
                'to' => $organizations->lastItem(),
            ],
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Display all contacts.
     */
    public function contacts(Request $request)
    {
        $query = Contact::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $contacts = $query->latest()
            ->with('organization:id,name')
            ->select('id', 'organization_id', 'name', 'email', 'phone', 'status', 'created_at')
            ->paginate($perPage)
            ->withQueryString();

        $contactsData = $contacts->map(function ($contact) {
            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'status' => $contact->status,
                'created_at' => $contact->created_at->toIso8601String(),
                'organization' => $contact->organization ? [
                    'id' => $contact->organization->id,
                    'name' => $contact->organization->name,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Contacts', [
            'contacts' => $contactsData,
            'pagination' => [
                'current_page' => $contacts->currentPage(),
                'last_page' => $contacts->lastPage(),
                'per_page' => $contacts->perPage(),
                'total' => $contacts->total(),
                'from' => $contacts->firstItem(),
                'to' => $contacts->lastItem(),
            ],
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Display all meetings.
     */
    public function meetings(Request $request)
    {
        $query = Meeting::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $meetings = $query->latest()
            ->with('organization:id,name')
            ->select('id', 'organization_id', 'title', 'description', 'start_time', 'end_time', 'status', 'created_at')
            ->paginate($perPage)
            ->withQueryString();

        $meetingsData = $meetings->map(function ($meeting) {
            return [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'description' => $meeting->description,
                'start_time' => $meeting->start_time ? $meeting->start_time->toIso8601String() : null,
                'end_time' => $meeting->end_time ? $meeting->end_time->toIso8601String() : null,
                'status' => $meeting->status,
                'created_at' => $meeting->created_at->toIso8601String(),
                'organization' => $meeting->organization ? [
                    'id' => $meeting->organization->id,
                    'name' => $meeting->organization->name,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Meetings', [
            'meetings' => $meetingsData,
            'pagination' => [
                'current_page' => $meetings->currentPage(),
                'last_page' => $meetings->lastPage(),
                'per_page' => $meetings->perPage(),
                'total' => $meetings->total(),
                'from' => $meetings->firstItem(),
                'to' => $meetings->lastItem(),
            ],
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Display all surveys.
     */
    public function surveys(Request $request)
    {
        $query = Survey::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $surveys = $query->latest()
            ->with('organization:id,name')
            ->select('id', 'organization_id', 'title', 'description', 'status', 'created_at')
            ->paginate($perPage)
            ->withQueryString();

        $surveysData = $surveys->map(function ($survey) {
            return [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
                'status' => $survey->status,
                'created_at' => $survey->created_at->toIso8601String(),
                'organization' => $survey->organization ? [
                    'id' => $survey->organization->id,
                    'name' => $survey->organization->name,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Surveys', [
            'surveys' => $surveysData,
            'pagination' => [
                'current_page' => $surveys->currentPage(),
                'last_page' => $surveys->lastPage(),
                'per_page' => $surveys->perPage(),
                'total' => $surveys->total(),
                'from' => $surveys->firstItem(),
                'to' => $surveys->lastItem(),
            ],
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }
}
