<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Contact;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeleteAccountController extends Controller
{
    /**
     * Permanently delete the authenticated user's account.
     * POST /api/account/delete
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login.',
                    'data' => null
                ], 401);
            }

            // Check if account is already deleted
            if ($user->account_deleted_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is already deleted',
                    'data' => null
                ], 400);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is required for account deletion',
                    'data' => null
                ], 400);
            }

            $password = $request->input('password');

            // Verify password
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect',
                    'data' => null
                ], 403);
            }

            $userEmail = $user->email;
            $userName = $user->name;

            // Start transaction
            DB::beginTransaction();

            try {
                // Soft delete user account
                $user->account_deleted_at = Carbon::now();
                $user->save();

                // Invalidate all user tokens
                try {
                    DB::table('personal_access_tokens')
                        ->where('tokenable_id', $user->id)
                        ->where('tokenable_type', 'App\Models\User')
                        ->delete();
                } catch (\Exception $e) {
                    Log::warning('Failed to invalidate user tokens during account deletion', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Delete or anonymize related data
                $this->deleteUserData($user->id);

                DB::commit();

                // Send confirmation email
                try {
                    Mail::send('emails.account-deletion-confirmation', [
                        'userName' => $userName,
                        'deletedAt' => Carbon::now()->format('Y-m-d H:i:s'),
                    ], function ($message) use ($userEmail, $userName) {
                        $message->to($userEmail, $userName ?? 'User')
                            ->subject('Account Deletion Confirmation');
                    });

                    Log::info('Account deletion confirmation email sent', [
                        'user_id' => $user->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send account deletion confirmation email', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info('Account deleted successfully', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Account deleted successfully',
                    'data' => null
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Failed to delete account', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account',
                'data' => null
            ], 500);
        }
    }

    /**
     * Delete or anonymize user-related data
     */
    private function deleteUserData($userId)
    {
        try {
            // Get user's organization
            $organization = DB::table('organization_users')
                ->where('user_id', $userId)
                ->first();

            if ($organization) {
                $organizationId = $organization->organization_id;

                // Delete meetings created by user
                Meeting::where('created_by', $userId)
                    ->where('organization_id', $organizationId)
                    ->delete();

                // Delete contacts created by user
                Contact::where('created_by', $userId)
                    ->where('organization_id', $organizationId)
                    ->delete();

                // Delete surveys created by user
                Survey::where('created_by', $userId)
                    ->where('organization_id', $organizationId)
                    ->delete();

                // Delete user profiles
                DB::table('user_profiles')
                    ->where('user_id', $userId)
                    ->where('organization_id', $organizationId)
                    ->delete();

                // Delete notification preferences
                DB::table('notification_preferences')
                    ->where('user_id', $userId)
                    ->delete();

                // Delete FCM tokens
                DB::table('fcm_tokens')
                    ->where('user_id', $userId)
                    ->delete();

                // Delete meeting notifications
                DB::table('meeting_notifications')
                    ->whereIn('meeting_id', function($query) use ($userId, $organizationId) {
                        $query->select('id')
                            ->from('meetings')
                            ->where('created_by', $userId)
                            ->where('organization_id', $organizationId);
                    })
                    ->delete();

                // Remove user from organization
                DB::table('organization_users')
                    ->where('user_id', $userId)
                    ->where('organization_id', $organizationId)
                    ->delete();
            }

            Log::info('User data deleted', [
                'user_id' => $userId,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete user data', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
