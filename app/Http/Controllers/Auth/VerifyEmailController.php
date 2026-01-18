<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    /**
     * Verify email using token
     */
    public function verify(Request $request, string $token)
    {
        // Rate limiting: 10 attempts per IP per hour
        $key = 'email-verify:' . $request->ip();
        $maxAttempts = 10;
        $decaySeconds = 3600; // 1 hour

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Too many verification attempts. Please try again later.',
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        // Find users with unverified emails and valid tokens (not expired)
        $users = User::whereNotNull('email_verification_token')
            ->whereNull('email_verified_at')
            ->whereNotNull('email_verification_sent_at')
            ->where('email_verification_sent_at', '>', now()->subHours(24))
            ->get();

        $verified = false;
        $user = null;

        foreach ($users as $u) {
            if ($u->verifyEmail($token)) {
                $verified = true;
                $user = $u;
                break;
            }
        }

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification token. Please request a new verification email.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully! You can now login to your account.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                ],
            ],
        ], 200);
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Rate limiting: 3 requests per email per hour
        $key = 'email-verify-resend:' . $request->email;
        $maxAttempts = 3;
        $decaySeconds = 3600; // 1 hour

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Too many resend attempts. Please try again later.',
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Don't reveal if user exists (security best practice)
            return response()->json([
                'success' => true,
                'message' => 'If that email address exists and is not verified, we have sent a verification email.',
            ], 200);
        }

        // If already verified, don't send email but return success
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Your email is already verified. You can login to your account.',
            ], 200);
        }

        // Generate new verification token
        $token = $user->generateEmailVerificationToken();

        // Build verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['token' => $token]
        );

        // Alternative: Use API endpoint format
        $appUrl = config('app.url');
        $verificationUrl = $appUrl . '/api/email/verify/' . $token;

        // Send verification email
        try {
            $emailVerification = new EmailVerification($user, $verificationUrl);
            $emailVerification->send();

            \Log::info('Verification email resent', [
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification email has been sent. Please check your inbox.',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to resend verification email', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again later.',
            ], 500);
        }
    }
}
