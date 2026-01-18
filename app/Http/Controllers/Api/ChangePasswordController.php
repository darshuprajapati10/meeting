<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChangePasswordController extends Controller
{
    /**
     * Change the authenticated user's password.
     * POST /api/account/change-password
     */
    public function changePassword(Request $request)
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
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|max:128',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password and new password are required. New password must be at least 8 characters.',
                    'data' => null
                ], 400);
            }

            $currentPassword = $request->input('current_password');
            $newPassword = $request->input('new_password');

            // Verify current password
            if (!Hash::check($currentPassword, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                    'data' => null
                ], 403);
            }

            // Check if new password is same as current
            if (Hash::check($newPassword, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New password must be different from current password',
                    'data' => null
                ], 400);
            }

            // Validate password strength
            $passwordStrength = $this->validatePasswordStrength($newPassword);
            if (!$passwordStrength['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $passwordStrength['message'],
                    'data' => null
                ], 400);
            }

            // Hash new password
            $newPasswordHash = Hash::make($newPassword);

            // Update password
            $user->password = $newPasswordHash;
            $user->password_changed_at = Carbon::now();
            $user->save();

            // Invalidate all user tokens (force re-login)
            try {
                DB::table('personal_access_tokens')
                    ->where('tokenable_id', $user->id)
                    ->where('tokenable_type', 'App\Models\User')
                    ->delete();
            } catch (\Exception $e) {
                Log::warning('Failed to invalidate user tokens', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send notification email
            try {
                Mail::send('emails.password-change-notification', [
                    'userName' => $user->name,
                    'changedAt' => Carbon::now()->format('Y-m-d H:i:s'),
                ], function ($message) use ($user) {
                    $message->to($user->email, $user->name ?? 'User')
                        ->subject('Password Changed Successfully');
                });

                Log::info('Password change notification email sent', [
                    'user_id' => $user->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send password change notification email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Password changed successfully', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to change password', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'data' => null
            ], 500);
        }
    }

    /**
     * Validate password strength
     */
    private function validatePasswordStrength($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (strlen($password) > 128) {
            $errors[] = 'Password must not exceed 128 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = 'Password must contain at least one special character (!@#$%^&*)';
        }

        if (!empty($errors)) {
            return [
                'valid' => false,
                'message' => implode(', ', $errors)
            ];
        }

        return ['valid' => true];
    }
}
