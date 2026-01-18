<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Contact;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\UserProfile;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportDataController extends Controller
{
    /**
     * Export user data
     * GET /api/account/export-data
     */
    public function exportData(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'data' => null
                ], 401);
            }

            // Check if account is deleted
            if ($user->account_deleted_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is deleted',
                    'data' => null
                ], 403);
            }

            // Rate limiting check (1 request per hour)
            $rateLimitCheck = $this->checkRateLimit($user->id);
            if ($rateLimitCheck) {
                return response()->json($rateLimitCheck, 429)
                    ->header('Retry-After', $rateLimitCheck['retry_after'] ?? 3600);
            }

            // Collect user data
            $exportData = [
                'user' => $this->getUserData($user),
                'contacts' => $this->getContactsData($user),
                'meetings' => $this->getMeetingsData($user),
                'organizations' => $this->getOrganizationsData($user),
                'user_profiles' => $this->getUserProfilesData($user),
                'notification_preferences' => $this->getNotificationPreferencesData($user),
                'exported_at' => Carbon::now()->toIso8601String(),
            ];

            // Generate filename
            $filename = 'user_' . $user->id . '_export_' . Carbon::now()->format('Ymd_His') . '.json';
            $filePath = 'exports/' . $filename;

            // Ensure exports directory exists
            if (!Storage::exists('exports')) {
                Storage::makeDirectory('exports');
            }

            // Store file with pretty JSON formatting
            Storage::put($filePath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Record export time for rate limiting
            $this->recordExportTime($user->id);

            // Generate download URL (expires in 24 hours)
            $expiresAt = Carbon::now()->addHours(24);
            
            // Use a route to download the file securely
            $baseUrl = config('app.url', 'http://10.110.125.173:8000');
            $downloadUrl = $baseUrl . '/api/account/export-data/download/' . $filename;

            Log::info('Data export created successfully', [
                'user_id' => $user->id,
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data export initiated successfully',
                'data' => [
                    'download_url' => $downloadUrl,
                    'expires_at' => $expiresAt->toIso8601String(),
                    'file_format' => 'json',
                    'file_size' => Storage::size($filePath),
                    'export_id' => 'exp_' . time(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to generate data export', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate export. Please try again later.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get user data
     */
    private function getUserData($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'bio' => $user->bio,
            'job_title' => $user->job_title,
            'department' => $user->department,
            'company' => $user->company,
            'profile_picture' => $user->profile_picture,
            'timezone' => $user->timezone,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at->toIso8601String(),
        ];
    }

    /**
     * Get contacts data for user's organization
     */
    private function getContactsData($user)
    {
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return [];
        }

        return Contact::where('organization_id', $organization->id)
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'company' => $contact->company,
                    'job_title' => $contact->job_title,
                    'address' => $contact->address,
                    'notes' => $contact->notes,
                    'groups' => $contact->groups,
                    'referrer_id' => $contact->referrer_id,
                    'created_at' => $contact->created_at->toIso8601String(),
                    'updated_at' => $contact->updated_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    /**
     * Get meetings data for user's organization
     */
    private function getMeetingsData($user)
    {
        $organization = $user->organizations()->first();
        
        if (!$organization) {
            return [];
        }

        return Meeting::where('organization_id', $organization->id)
            ->with(['attendees', 'survey'])
            ->get()
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'meeting_title' => $meeting->meeting_title,
                    'status' => $meeting->status,
                    'date' => $meeting->date?->format('Y-m-d'),
                    'time' => $meeting->time,
                    'duration' => $meeting->duration,
                    'meeting_type' => $meeting->meeting_type,
                    'custom_location' => $meeting->custom_location,
                    'agenda_notes' => $meeting->agenda_notes,
                    'survey_id' => $meeting->survey_id,
                    'attendees' => $meeting->attendees->map(function ($attendee) {
                        return [
                            'id' => $attendee->id,
                            'first_name' => $attendee->first_name,
                            'last_name' => $attendee->last_name,
                            'email' => $attendee->email,
                        ];
                    })->toArray(),
                    'created_at' => $meeting->created_at->toIso8601String(),
                    'updated_at' => $meeting->updated_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    /**
     * Get organizations data
     */
    private function getOrganizationsData($user)
    {
        return $user->organizations()
            ->get()
            ->map(function ($org) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'slug' => $org->slug,
                    'description' => $org->description,
                    'email' => $org->email,
                    'phone' => $org->phone,
                    'address' => $org->address,
                    'status' => $org->status,
                    'type' => $org->type,
                    'role' => $org->pivot->role ?? null,
                    'created_at' => $org->created_at->toIso8601String(),
                    'updated_at' => $org->updated_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    /**
     * Get user profiles data
     */
    private function getUserProfilesData($user)
    {
        return UserProfile::where('user_id', $user->id)
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'organization_id' => $profile->organization_id,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'bio' => $profile->bio,
                    'email_address' => $profile->email_address,
                    'address' => $profile->address,
                    'company' => $profile->company,
                    'phone' => $profile->phone,
                    'job_title' => $profile->job_title,
                    'department' => $profile->department,
                    'timezone' => $profile->timezone,
                    'created_at' => $profile->created_at->toIso8601String(),
                    'updated_at' => $profile->updated_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    /**
     * Get notification preferences data
     */
    private function getNotificationPreferencesData($user)
    {
        $preferences = NotificationPreference::where('user_id', $user->id)->first();
        
        if (!$preferences) {
            return null;
        }

        return [
            'push_notifications_enabled' => $preferences->push_notifications_enabled,
            'email_notifications_enabled' => $preferences->email_notifications_enabled,
            'email_meeting_reminders' => $preferences->email_meeting_reminders,
            'email_meeting_updates' => $preferences->email_meeting_updates,
            'email_meeting_cancellations' => $preferences->email_meeting_cancellations,
            'meeting_reminders' => $preferences->meeting_reminders,
            'notification_sound' => $preferences->notification_sound,
            'notification_badge' => $preferences->notification_badge,
            'created_at' => $preferences->created_at->toIso8601String(),
            'updated_at' => $preferences->updated_at->toIso8601String(),
        ];
    }

    /**
     * Check rate limit for export requests
     */
    private function checkRateLimit($userId)
    {
        $key = "user_export_time_{$userId}";
        $lastExportTime = Cache::get($key);

        if ($lastExportTime) {
            $lastExport = Carbon::parse($lastExportTime);
            $timeSinceLastExport = Carbon::now()->diffInSeconds($lastExport);

            // If less than 1 hour has passed
            if ($timeSinceLastExport < 3600) {
                $retryAfter = 3600 - $timeSinceLastExport;
                return [
                    'success' => false,
                    'message' => 'Too many export requests. Please try again later.',
                    'retry_after' => $retryAfter,
                    'data' => null
                ];
            }
        }

        return null;
    }

    /**
     * Record export time for rate limiting
     */
    private function recordExportTime($userId)
    {
        $key = "user_export_time_{$userId}";
        Cache::put($key, Carbon::now()->toIso8601String(), now()->addHour());
    }

    /**
     * Download exported data file
     * GET /api/account/export-data/download/{filename}
     */
    public function download(Request $request, $filename)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'data' => null
                ], 401);
            }

            // Verify file belongs to user
            if (!str_starts_with($filename, 'user_' . $user->id . '_export_')) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                    'data' => null
                ], 404);
            }

            $filePath = 'exports/' . $filename;

            if (!Storage::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found or expired',
                    'data' => null
                ], 404);
            }

            // Check if file is expired (older than 24 hours)
            $fileTime = Carbon::createFromTimestamp(Storage::lastModified($filePath));
            if ($fileTime->addHours(24)->isPast()) {
                // Delete expired file
                Storage::delete($filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'File has expired',
                    'data' => null
                ], 410);
            }

            Log::info('Export file downloaded', [
                'user_id' => $user->id,
                'filename' => $filename,
                'ip' => $request->ip(),
            ]);

            return Storage::download($filePath, $filename, [
                'Content-Type' => 'application/json',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to download export file', [
                'user_id' => $request->user()?->id,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to download file',
                'data' => null
            ], 500);
        }
    }
}
