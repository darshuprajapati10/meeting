<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserProfileRepository;
use App\Repositories\UserRepository;
use App\Http\Requests\StoreUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\UserProfile;
use App\Models\Meeting;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserProfileController extends Controller
{
    protected $userProfileRepository;
    protected $userRepository;

    public function __construct(UserProfileRepository $userProfileRepository, UserRepository $userRepository)
    {
        $this->userProfileRepository = $userProfileRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get paginated list of user profiles for the authenticated user's organization
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'per_page' => 15,
                    'to' => null,
                    'total' => 0,
                ],
                'message' => 'No organization found. Please create an organization first.',
            ]);
        }

        $organizationId = $organization->id;

        // Get pagination parameters from request body (JSON) or query string
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        // Validate and sanitize per_page (between 1 and 100)
        $perPage = (int) $perPage;
        $perPage = min(max(1, $perPage), 100);

        // Validate and sanitize page (must be at least 1)
        $page = (int) $page;
        $page = max(1, $page);

        // Get user profiles for this organization
        $userProfiles = $this->userProfileRepository->getByOrganization($organizationId, $perPage, $page);

        // Ensure requested page doesn't exceed last page
        $lastPage = $userProfiles->lastPage();
        if ($page > $lastPage && $lastPage > 0) {
            $userProfiles = $this->userProfileRepository->getByOrganization($organizationId, $perPage, $lastPage);
        }

        return response()->json([
            'data' => UserProfileResource::collection($userProfiles->items()),
            'meta' => [
                'current_page' => $userProfiles->currentPage(),
                'from' => $userProfiles->firstItem(),
                'last_page' => $userProfiles->lastPage(),
                'per_page' => $userProfiles->perPage(),
                'to' => $userProfiles->lastItem(),
                'total' => $userProfiles->total(),
            ],
            'message' => 'User profiles retrieved successfully.',
        ]);
    }

    /**
     * Get single user profile by ID
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:user_profiles,id',
        ]);

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create an organization first.',
            ], 404);
        }

        $organizationId = $organization->id;
        $userProfileId = $request->id;

        // Get user profile from user's organization with user relationship loaded
        $userProfile = UserProfile::where('id', $userProfileId)
            ->where('organization_id', $organizationId)
            ->with('user') // Load user relationship to get login data
            ->firstOrFail();

        return response()->json([
            'data' => new UserProfileResource($userProfile),
            'message' => 'User profile retrieved successfully.',
        ]);
    }

    /**
     * Create or update a user profile
     */
    public function save(StoreUserProfileRequest $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'message' => 'No organization found. Please create an organization first.',
            ], 404);
        }

        $organizationId = $organization->id;

        DB::beginTransaction();
        try {
            $data = $request->validated();
            // organization_id and user_id will be automatically set by UserProfile model events

            // If id is present: update existing user profile; else create
            if ($request->id) {
                // Verify user profile belongs to user's organization
                $userProfile = UserProfile::where('id', $request->id)
                    ->where('organization_id', $organizationId)
                    ->with('user') // Load user relationship to get login data
                    ->first();
                
                if (!$userProfile) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'User profile not found or you do not have permission to update it.',
                    ], 404);
                }

                $userProfile = $this->userProfileRepository->update($userProfile, $data);

                DB::commit();

                return response()->json([
                    'data' => new UserProfileResource($userProfile),
                    'message' => 'User profile updated successfully.',
                ], 200);
            } else {
                // Create new user profile
                $userProfile = $this->userProfileRepository->create($data);

                DB::commit();

                return response()->json([
                    'data' => new UserProfileResource($userProfile),
                    'message' => 'User profile created successfully.',
                ], 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while saving the user profile.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user profile by ID
     */
    public function delete(Request $request)
    {
        $user = $request->user();

        // Validate ID is provided
        $request->validate([
            'id' => 'required|integer|exists:user_profiles,id',
        ]);

        $userProfileId = $request->id;

        // First, check if user profile exists at all
        $userProfile = UserProfile::find($userProfileId);
        
        if (!$userProfile) {
            return response()->json([
                'message' => 'User profile not found.',
            ], 404);
        }

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        // Check permission: user profile must belong to user's organization
        if (!$organization || $userProfile->organization_id != $organization->id) {
            return response()->json([
                'message' => 'You do not have permission to delete this user profile.',
            ], 403);
        }

        // Delete the user profile
        $this->userProfileRepository->delete($userProfile);

        return response()->json([
            'message' => 'User profile deleted successfully.',
        ], 200);
    }

    /**
     * Get user profile quick stats (Meetings This Month, Total Contacts, Hours Scheduled, Meeting Rating)
     */
    public function state(Request $request)
    {
        $user = $request->user();

        // Get profile stats from UserRepository
        $stats = $this->userRepository->getProfileStats($user);

        return response()->json([
            'data' => [
                'meetings_this_month' => $stats['meetings_this_month'],
                'total_contacts' => $stats['total_contacts'],
                'hours_scheduled' => $stats['hours_scheduled'],
                'meeting_rating' => $stats['meeting_rating'],
            ],
            'message' => 'User profile statistics retrieved successfully.',
        ]);
    }

    /**
     * Get user profile recent activities
     */
    public function activity(Request $request)
    {
        $user = $request->user();

        // Get organization_id from user's organizations
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return response()->json([
                'data' => [],
                'message' => 'No organization found. Please create an organization first.',
            ]);
        }

        $organizationId = $organization->id;
        $activities = [];

        // Get recent scheduled meetings (last 30 days)
        $scheduledMeetings = Meeting::where('organization_id', $organizationId)
            ->where('status', 'Scheduled')
            ->where('created_by', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->with(['attendees' => function($query) {
                $query->limit(1); // Get first attendee for display
            }])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($scheduledMeetings as $meeting) {
            $attendeeName = 'contact';
            if ($meeting->attendees->count() > 0) {
                $firstAttendee = $meeting->attendees->first();
                $attendeeName = $firstAttendee->first_name . ' ' . $firstAttendee->last_name;
            }
            
            $activities[] = [
                'type' => 'meeting_scheduled',
                'description' => 'Scheduled meeting with ' . $attendeeName,
                'timestamp' => $meeting->created_at,
                'human_time' => $this->getHumanTime($meeting->created_at),
            ];
        }

        // Get recent completed meetings (last 30 days)
        $completedMeetings = Meeting::where('organization_id', $organizationId)
            ->where('status', 'Completed')
            ->where('created_by', $user->id)
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($completedMeetings as $meeting) {
            $activities[] = [
                'type' => 'meeting_completed',
                'description' => 'Completed ' . $meeting->meeting_title . ' meeting',
                'timestamp' => $meeting->updated_at,
                'human_time' => $this->getHumanTime($meeting->updated_at),
            ];
        }

        // Get recent contact updates (where updated_at != created_at, last 30 days)
        $updatedContacts = Contact::where('organization_id', $organizationId)
            ->where('created_by', $user->id)
            ->whereColumn('updated_at', '!=', 'created_at')
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($updatedContacts as $contact) {
            $activities[] = [
                'type' => 'contact_updated',
                'description' => 'Updated contact information',
                'timestamp' => $contact->updated_at,
                'human_time' => $this->getHumanTime($contact->updated_at),
            ];
        }

        // Get recent contact additions (grouped by day, last 30 days)
        $recentContacts = Contact::where('organization_id', $organizationId)
            ->where('created_by', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($contact) {
                return $contact->created_at->format('Y-m-d');
            });

        foreach ($recentContacts as $date => $contacts) {
            $count = $contacts->count();
            $latestContact = $contacts->first();
            
            $activities[] = [
                'type' => 'contacts_added',
                'description' => 'Added ' . $count . ' new contact' . ($count > 1 ? 's' : ''),
                'timestamp' => $latestContact->created_at,
                'human_time' => $this->getHumanTime($latestContact->created_at),
            ];
        }

        // Sort all activities by timestamp (most recent first)
        usort($activities, function($a, $b) {
            return $b['timestamp']->timestamp <=> $a['timestamp']->timestamp;
        });

        // Limit to 20 most recent activities
        $activities = array_slice($activities, 0, 20);

        // Format timestamps for response
        $formattedActivities = array_map(function($activity) {
            return [
                'type' => $activity['type'],
                'description' => $activity['description'],
                'timestamp' => $activity['timestamp']->toISOString(),
                'human_time' => $activity['human_time'],
            ];
        }, $activities);

        return response()->json([
            'data' => $formattedActivities,
            'message' => 'User profile activities retrieved successfully.',
        ]);
    }

    /**
     * Get human-readable time difference
     */
    private function getHumanTime($timestamp): string
    {
        if (!$timestamp instanceof Carbon) {
            $timestamp = Carbon::parse($timestamp);
        }

        $now = Carbon::now();
        $diffInSeconds = abs($now->diffInSeconds($timestamp));

        // If less than 60 seconds, show "1 minute ago"
        if ($diffInSeconds < 60) {
            return '1 minute ago';
        } elseif ($diffInSeconds < 3600) {
            $minutes = floor(abs($now->diffInMinutes($timestamp)));
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diffInSeconds < 86400) {
            $hours = floor(abs($now->diffInHours($timestamp)));
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diffInSeconds < 2592000) {
            $days = floor(abs($now->diffInDays($timestamp)));
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            $months = floor(abs($now->diffInMonths($timestamp)));
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        }
    }
}
