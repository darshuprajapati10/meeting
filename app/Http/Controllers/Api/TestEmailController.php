<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZeptoMailService;
use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TestEmailController extends Controller
{
    /**
     * Test ZeptoMail email sending
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $zeptomailService = new ZeptoMailService();
            
            $htmlBody = '
                <h1>ZeptoMail Test Email</h1>
                <p>This is a test email to verify ZeptoMail integration is working correctly.</p>
                <p><strong>Sent at:</strong> ' . now()->toDateTimeString() . '</p>
                <p>If you received this email, ZeptoMail integration is working!</p>
            ';

            $result = $zeptomailService->sendEmail(
                $request->email,
                'ZeptoMail Test Email - Ongoing Forge',
                $htmlBody
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test email sent successfully! Check your inbox.',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send email. Check logs for details.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Test email error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test email verification email
     */
    public function testVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Get or create test user
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => 'Test User',
                    'email' => $request->email,
                    'password' => Hash::make('password'),
                ]);
            }

            // Generate verification token
            $token = $user->generateEmailVerificationToken();
            $appUrl = config('app.url', 'http://localhost');
            $verificationUrl = $appUrl . '/api/email/verify/' . $token;

            // Send verification email
            $emailVerification = new EmailVerification($user, $verificationUrl);
            $result = $emailVerification->send();

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verification email sent successfully! Check your inbox.',
                    'data' => [
                        'verification_url' => $verificationUrl,
                        'token' => $token, // Only for testing
                    ],
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send verification email. Check logs for details.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Test verification email error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
