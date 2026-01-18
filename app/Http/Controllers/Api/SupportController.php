<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SupportController extends Controller
{
    /**
     * Submit a support request, bug report, or feature request.
     * POST /api/support/contact
     */
    public function contact(Request $request)
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
                'subject' => 'required|string|min:1|max:500',
                'message' => 'required|string|min:10|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 400);
            }

            // Check rate limit
            $rateLimitCheck = $this->checkRateLimit($user->id);
            if ($rateLimitCheck) {
                return response()->json($rateLimitCheck, 429);
            }

            // Create support message
            $supportMessage = SupportMessage::create([
                'user_id' => $user->id,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'pending',
                'priority' => 'normal',
            ]);

            // Send email notification to support team (optional - don't fail if email fails)
            try {
                $fromAddress = config('mail.from.address');
                $supportEmail = config('mail.support_email', $fromAddress);
                
                if ($supportEmail && $fromAddress) {
                    Mail::send('emails.support-message-received', [
                        'supportMessage' => $supportMessage,
                        'user' => $user,
                        'adminUrl' => config('app.url', 'http://10.110.125.173:8000') . '/admin/support/messages/' . $supportMessage->id,
                    ], function ($message) use ($supportEmail, $supportMessage) {
                        $message->to($supportEmail)
                            ->subject('New Support Request: ' . $supportMessage->subject);
                    });

                    Log::info('Support message notification email sent', [
                        'user_id' => $user->id,
                        'message_id' => $supportMessage->id,
                        'support_email' => $supportEmail,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send support message notification email', [
                    'user_id' => $user->id,
                    'message_id' => $supportMessage->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the request if email fails
            }

            // Send confirmation email to user (optional - don't fail if email fails)
            try {
                $fromAddress = config('mail.from.address');
                if ($fromAddress) {
                    Mail::send('emails.support-message-confirmation', [
                        'supportMessage' => $supportMessage,
                        'user' => $user,
                    ], function ($message) use ($request, $user) {
                        $message->to($request->email, $user->name ?? 'User')
                            ->subject('We\'ve received your support request');
                    });

                    Log::info('Support message confirmation email sent', [
                        'user_id' => $user->id,
                        'message_id' => $supportMessage->id,
                        'email' => $request->email,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send support message confirmation email', [
                    'user_id' => $user->id,
                    'message_id' => $supportMessage->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the request if email fails
            }

            Log::info('Support message created successfully', [
                'user_id' => $user->id,
                'message_id' => $supportMessage->id,
                'subject' => $supportMessage->subject,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent. We\'ll get back to you soon!',
                'data' => [
                    'id' => $supportMessage->id,
                    'email' => $supportMessage->email,
                    'subject' => $supportMessage->subject,
                    'status' => $supportMessage->status,
                    'created_at' => $supportMessage->created_at->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to process support message', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send support message. Please try again later.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Check rate limit for support messages.
     * 
     * @param int $userId
     * @return array|null Returns error response if rate limit exceeded, null otherwise
     */
    private function checkRateLimit($userId)
    {
        $hourlyLimit = 5;
        $dailyLimit = 20;

        // Check hourly limit
        $hourlyCount = SupportMessage::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        if ($hourlyCount >= $hourlyLimit) {
            return [
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'data' => null
            ];
        }

        // Check daily limit
        $dailyCount = SupportMessage::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->startOfDay())
            ->count();

        if ($dailyCount >= $dailyLimit) {
            return [
                'success' => false,
                'message' => 'Daily message limit reached. Please try again tomorrow.',
                'data' => null
            ];
        }

        return null;
    }
}
