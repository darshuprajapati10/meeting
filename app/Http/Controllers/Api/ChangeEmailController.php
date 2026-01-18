<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChangeEmailController extends Controller
{
    /**
     * Request to change the authenticated user's email address.
     * POST /api/account/change-email
     */
    public function changeEmail(Request $request)
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

            // Check if account is deleted
            if ($user->account_deleted_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is deleted',
                    'data' => null
                ], 403);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email format',
                    'data' => null
                ], 400);
            }

            $newEmail = $request->input('email');

            // Check if new email is same as current email
            if (strtolower($newEmail) === strtolower($user->email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New email must be different from current email',
                    'data' => null
                ], 400);
            }

            // Check if email is already in use by another active user
            $existingUser = User::where('email', $newEmail)
                ->where('id', '!=', $user->id)
                ->whereNull('account_deleted_at')
                ->first();
                
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already in use',
                    'data' => null
                ], 409);
            }

            // Update email immediately (without verification)
            $oldEmail = $user->email;
            $user->email = $newEmail;
            $user->email_verified_at = Carbon::now();
            
            // Clear any pending email change requests
            $user->email_change_token = null;
            $user->email_change_token_expires_at = null;
            $user->email_change_new_email = null;
            $user->email_change_requested_at = null;
            $user->save();

            // Refresh user model to ensure email is updated
            $user->refresh();

            // Also update email_address in user_profiles if they exist
            UserProfile::where('user_id', $user->id)
                ->update(['email_address' => $newEmail]);

            // Send notification email to old email (optional)
            try {
                Mail::send('emails.email-change-notification', [
                    'oldEmail' => $oldEmail,
                    'newEmail' => $newEmail,
                    'userName' => $user->name,
                ], function ($message) use ($oldEmail, $user) {
                    $message->to($oldEmail, $user->name ?? 'User')
                        ->subject('Email Changed Successfully');
                });

                Log::info('Email change notification email sent', [
                    'user_id' => $user->id,
                    'old_email' => $oldEmail,
                    'new_email' => $newEmail,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send email change notification email', [
                    'user_id' => $user->id,
                    'old_email' => $oldEmail,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Email changed successfully', [
                'user_id' => $user->id,
                'old_email' => $oldEmail,
                'new_email' => $newEmail,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email changed successfully',
                'data' => [
                    'email' => $newEmail,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to process email change request', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process email change request',
                'data' => null
            ], 500);
        }
    }

    /**
     * Verify and complete the email change process.
     * GET /api/account/verify-email-change?token={token}
     */
    public function verifyEmailChange(Request $request)
    {
        try {
            $token = $request->query('token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is required',
                    'data' => null
                ], 400);
            }

            // Find user by token
            $user = User::where('email_change_token', $token)
                ->whereNull('account_deleted_at')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                    'data' => null
                ], 400);
            }

            // Check if token is expired
            if ($user->email_change_token_expires_at && Carbon::now()->gt($user->email_change_token_expires_at)) {
                // Clear expired token
                $user->email_change_token = null;
                $user->email_change_token_expires_at = null;
                $user->email_change_new_email = null;
                $user->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired. Please request a new email change.',
                    'data' => null
                ], 400);
            }

            // Check if new email is still available
            $newEmail = $user->email_change_new_email;
            if (!$newEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending email change request found',
                    'data' => null
                ], 400);
            }

            // Check if new email is already in use by another user
            $existingUser = User::where('email', $newEmail)
                ->where('id', '!=', $user->id)
                ->whereNull('account_deleted_at')
                ->first();

            if ($existingUser) {
                // Clear the request
                $user->email_change_token = null;
                $user->email_change_token_expires_at = null;
                $user->email_change_new_email = null;
                $user->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Email is already in use by another account',
                    'data' => null
                ], 409);
            }

            // Update email
            $oldEmail = $user->email;
            $user->email = $newEmail;
            $user->email_verified_at = Carbon::now();
            $user->email_change_token = null;
            $user->email_change_token_expires_at = null;
            $user->email_change_new_email = null;
            $user->email_change_requested_at = null;
            $user->save();

            Log::info('Email change verified and completed', [
                'user_id' => $user->id,
                'old_email' => $oldEmail,
                'new_email' => $newEmail,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email changed successfully',
                'data' => [
                    'email' => $newEmail,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to verify email change', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify email change',
                'data' => null
            ], 500);
        }
    }
}
