<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\GoogleLoginRequest;
use App\Repositories\UserRepository;
use App\Services\SubscriptionService;
use App\Mail\EmailVerification;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected $userRepository;

    public function __construct(
        UserRepository $userRepository,
        private SubscriptionService $subscriptionService
    ) {
        $this->userRepository = $userRepository;
    }
    /**
     * Login user and create token
     */
    public function login(LoginRequest $request) {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before logging in. Check your inbox for the verification email.',
                'requires_verification' => true,
                'data' => [
                    'email' => $user->email,
                    'can_resend' => true,
                ],
            ], 403);
        }

        // Auto-create user profile if doesn't exist
        $organization = $user->organizations()->first();
        if ($organization) {
            $existingProfile = \App\Models\UserProfile::where('user_id', $user->id)
                ->where('organization_id', $organization->id)
                ->first();
            
            if (!$existingProfile) {
                // Split name into first_name and last_name
                $nameParts = explode(' ', $user->name, 2);
                \App\Models\UserProfile::create([
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'email_address' => $user->email,
                    'address' => '',  // Required field
                    'company' => '',  // Required field
                ]);
            }
        }

        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        // Calculate expiration timestamp (30 days from now)
        $expiresAt = now()->addMinutes(config('sanctum.expiration', 43200));

        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'token' => $token,
                'expires_at' => $expiresAt->toIso8601String(),
                'expires_in_seconds' => config('sanctum.expiration', 43200) * 60,
            ],
            'message' => 'Login successfully!',
        ]);
    }

    /**
     * Signup user and create organization if needed
     */
    public function signup(SignupRequest $request)
    {
        // Create organization if it doesn't exist
        // Use organization_name if provided, otherwise use user's name
        $organizationName = $request->organization_name ?? $request->name . "'s Organization";
        $slug = Str::slug($organizationName);
        
        // Ensure slug is not empty
        if (empty($slug)) {
            $slug = 'organization-' . time();
        }
        
        $organization = Organization::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $organizationName,
                'description' => 'Organization created during registration',
                'status' => 'active',
            ]
        );

        // Create Pro trial subscription for new organizations
        if ($organization->wasRecentlyCreated) {
            try {
                $this->subscriptionService->createProTrialSubscription($organization);
            } catch (\Exception $e) {
                \Log::error('Failed to create Pro trial subscription during signup', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with signup even if subscription creation fails
            }
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile' => $request->mobile,
        ]);

        // Attach user to organization with admin role
        $user->organizations()->attach($organization->id, ['role' => 'admin']);

        // Generate email verification token and send verification email
        try {
            $verificationToken = $user->generateEmailVerificationToken();
            $appUrl = config('app.url');
            $verificationUrl = $appUrl . '/api/email/verify/' . $verificationToken;

            $emailVerification = new EmailVerification($user, $verificationUrl);
            $emailVerification->send();

            \Log::info('Verification email sent during signup', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email during signup', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            // Continue with signup even if email sending fails
        }

        // Don't create token yet - user must verify email first
        // Return response indicating email verification is required
        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'organization' => new OrganizationResource($organization),
            ],
            'message' => 'Signup successful! Please check your email to verify your account before logging in.',
            'requires_verification' => true,
        ], 201);
    }

    /**
     * Send password reset code
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // Rate limiting: 3 requests per email per hour
        $key = 'forgot-password:' . $request->email;
        $maxAttempts = 3;
        $decaySeconds = 3600; // 1 hour

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Too many password reset attempts. Please try again later.',
                'retry_after' => $seconds,
            ], 429);
        }

        // Rate limiting: 10 requests per IP per hour
        $ipKey = 'forgot-password-ip:' . $request->ip();
        $ipMaxAttempts = 10;

        if (RateLimiter::tooManyAttempts($ipKey, $ipMaxAttempts)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many password reset attempts. Please try again later.',
                'retry_after' => $seconds,
            ], 429);
        }

        // Increment rate limiter
        RateLimiter::hit($key, $decaySeconds);
        RateLimiter::hit($ipKey, $decaySeconds);

        // Check if user exists (but don't reveal if they don't)
        $user = User::where('email', $request->email)->first();

        // Always proceed to prevent email enumeration
        // Generate secure 6-digit code
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in password_reset_tokens table
        // Code expires in 60 minutes
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        // Send code via email if user exists
        if ($user) {
            try {
                $mailSent = Mail::send('emails.password-reset-code', [
                    'code' => $code,
                    'user' => $user,
                    'expiresIn' => 60, // minutes
                ], function ($message) use ($user) {
                    $message->to($user->email, $user->name ?? 'User')
                        ->subject('Password Reset Code');
                });

                if ($mailSent) {
                    \Log::info('Password reset code email sent successfully', [
                        'email' => $request->email,
                        'ip' => $request->ip(),
                        'code_generated' => true,
                        'mail_driver' => config('mail.default'),
                    ]);
                } else {
                    \Log::warning('Password reset code email sending returned false', [
                        'email' => $request->email,
                        'ip' => $request->ip(),
                        'mail_driver' => config('mail.default'),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send password reset code email', [
                    'email' => $request->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'mail_driver' => config('mail.default'),
                ]);
            }
        } else {
            \Log::info('Password reset code generated (user not found, but code stored for security)', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);
        }

        // Always return success to prevent email enumeration
        // This is a security best practice
        $response = [
            'success' => true,
            'message' => 'If that email address exists in our system, we have sent a password reset code to it.',
        ];

        // In development/testing, include code in response (remove in production)
        if (config('app.debug') && app()->environment('local')) {
            $response['debug'] = [
                'code' => $code,
                'note' => 'This is only shown in local development mode',
            ];
        }

        return response()->json($response, 200);
    }

    /**
     * Google OAuth login
     */
    public function googleLogin(GoogleLoginRequest $request)
    {
        try {
            // Verify Google ID token using Google's tokeninfo endpoint
            $googleClientId = config('services.google.client_id');
            
            if (!$googleClientId || $googleClientId === 'your-google-client-id.apps.googleusercontent.com' || trim($googleClientId) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Client ID is not configured. Please set GOOGLE_CLIENT_ID in your .env file.'
                ], 500);
            }

            // Clean and validate token
            $idToken = trim($request->id_token);
            
            // Verify token with Google
            // Build URL manually to ensure proper encoding
            $tokenInfoUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
            
            $response = Http::timeout(10)->get($tokenInfoUrl);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $statusCode = $response->status();
                
                \Log::error('Google token verification failed', [
                    'status' => $statusCode,
                    'response' => $errorBody,
                    'token_length' => strlen($idToken),
                    'token_preview' => substr($idToken, 0, 50) . '...'
                ]);
                
                $errorMessage = $errorBody['error_description'] ?? $errorBody['error'] ?? 'Invalid Google token. Please try again.';
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'debug' => [
                        'status_code' => $statusCode,
                        'google_error' => $errorBody['error'] ?? null,
                        'google_error_description' => $errorBody['error_description'] ?? null,
                    ]
                ], 401);
            }

            $payload = $response->json();

            // Verify the audience (client ID) matches
            $tokenClientId = $payload['aud'] ?? null;
            if (!isset($payload['aud']) || $payload['aud'] !== $googleClientId) {
                \Log::warning('Google Login - Client ID Mismatch', [
                    'configured' => $googleClientId,
                    'token_audience' => $tokenClientId,
                    'token_azp' => $payload['azp'] ?? null,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token. Client ID mismatch.',
                    'debug' => [
                        'configured_client_id' => $googleClientId,
                        'token_client_id' => $tokenClientId,
                        'hint' => 'Make sure the token was issued for the same Client ID as configured in .env file'
                    ]
                ], 401);
            }

            // Verify email matches
            $googleEmail = $payload['email'] ?? null;
            if (!$googleEmail || $googleEmail !== $request->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email mismatch. The provided email does not match the Google account.'
                ], 422);
            }

            // Restrict to single email (if configured)
            $allowedEmail = config('services.google.login_allowed_email');
            if ($allowedEmail && $googleEmail !== $allowedEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is not authorized to login via Google.'
                ], 403);
            }

            // Extract user info from token
            $googleId = $payload['sub'] ?? $request->google_id;
            $name = $payload['name'] ?? $request->name;
            $email = $googleEmail;

            // Check if user exists by email or google_id
            $user = $this->userRepository->findByEmail($email);
            
            if (!$user && $googleId) {
                $user = $this->userRepository->findByGoogleId($googleId);
            }

            if ($user) {
                // Existing user - update google_id if not set
                if (!$user->google_id && $googleId) {
                    $user->update(['google_id' => $googleId]);
                }

                // Auto-create organization if doesn't exist
                $organization = $user->organizations()->first();
                
                if (!$organization) {
                    // Create personal organization for the user
                    $userName = $user->name ?? $user->email ?? 'User';
                    $organizationName = $userName . "'s Organization";
                    $slug = Str::slug($organizationName . '-' . $user->id);
                    
                    // Ensure slug is not empty
                    if (empty($slug)) {
                        $slug = 'organization-' . $user->id . '-' . time();
                    }
                    
                    $organization = Organization::create([
                        'name' => $organizationName,
                        'slug' => $slug,
                        'description' => 'Personal organization created automatically',
                        'status' => 'active',
                    ]);
                    
                    // Attach user to organization with admin role
                    $user->organizations()->attach($organization->id, ['role' => 'admin']);

                    // Create Pro trial subscription for new organization
                    try {
                        $this->subscriptionService->createProTrialSubscription($organization);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create Pro trial subscription during Google OAuth (existing user)', [
                            'organization_id' => $organization->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Continue even if subscription creation fails
                    }
                }

                // Auto-create user profile if doesn't exist
                $existingProfile = \App\Models\UserProfile::where('user_id', $user->id)
                    ->where('organization_id', $organization->id)
                    ->first();
                
                if (!$existingProfile) {
                    // Split name into first_name and last_name
                    $nameParts = explode(' ', $user->name, 2);
                    \App\Models\UserProfile::create([
                        'user_id' => $user->id,
                        'organization_id' => $organization->id,
                        'first_name' => $nameParts[0] ?? '',
                        'last_name' => $nameParts[1] ?? '',
                        'email_address' => $user->email,
                        'address' => '',  // Required field
                        'company' => '',  // Required field
                    ]);
                }

                // Login existing user
                $tokenResult = $user->createToken('auth_token');
                $token = $tokenResult->plainTextToken;

                // Calculate expiration timestamp (30 days from now)
                $expiresAt = now()->addMinutes(config('sanctum.expiration', 43200));

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'email_verified_at' => $user->email_verified_at,
                            'created_at' => $user->created_at,
                            'updated_at' => $user->updated_at,
                        ],
                        'token' => $token,
                        'token_type' => 'Bearer',
                        'expires_at' => $expiresAt->toIso8601String(),
                        'expires_in_seconds' => config('sanctum.expiration', 43200) * 60,
                    ]
                ], 200);
            } else {
                // New user - create account
                $user = $this->userRepository->createFromGoogle([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                ]);

                // Auto-create organization if doesn't exist
                $organization = $user->organizations()->first();
                
                if (!$organization) {
                    // Create personal organization for the user
                    $userName = $user->name ?? $user->email ?? 'User';
                    $organizationName = $userName . "'s Organization";
                    $slug = Str::slug($organizationName . '-' . $user->id);
                    
                    // Ensure slug is not empty
                    if (empty($slug)) {
                        $slug = 'organization-' . $user->id . '-' . time();
                    }
                    
                    $organization = Organization::create([
                        'name' => $organizationName,
                        'slug' => $slug,
                        'description' => 'Personal organization created automatically',
                        'status' => 'active',
                    ]);
                    
                    // Attach user to organization with admin role
                    $user->organizations()->attach($organization->id, ['role' => 'admin']);

                    // Create Pro trial subscription for new organization
                    try {
                        $this->subscriptionService->createProTrialSubscription($organization);
                    } catch (\Exception $e) {
                        \Log::error('Failed to create Pro trial subscription during Google OAuth (new user)', [
                            'organization_id' => $organization->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Continue even if subscription creation fails
                    }
                }

                // Auto-create user profile for new user
                $nameParts = explode(' ', $user->name, 2);
                \App\Models\UserProfile::create([
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'email_address' => $user->email,
                    'address' => '',  // Required field
                    'company' => '',  // Required field
                ]);

                $tokenResult = $user->createToken('auth_token');
                $token = $tokenResult->plainTextToken;

                // Calculate expiration timestamp (30 days from now)
                $expiresAt = now()->addMinutes(config('sanctum.expiration', 43200));

                return response()->json([
                    'success' => true,
                    'message' => 'Account created and logged in successfully',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'email_verified_at' => $user->email_verified_at,
                            'created_at' => $user->created_at,
                            'updated_at' => $user->updated_at,
                        ],
                        'token' => $token,
                        'token_type' => 'Bearer',
                        'expires_at' => $expiresAt->toIso8601String(),
                        'expires_in_seconds' => config('sanctum.expiration', 43200) * 60,
                    ]
                ], 201);
            }
        } catch (\Exception $e) {
            \Log::error('Google login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again later.'
            ], 500);
        }
    }

    /**
     * Refresh authentication token
     * Revokes the current token and issues a new one
     */
    public function refreshToken(\Illuminate\Http\Request $request)
    {
        // Get the current user (authenticated via Sanctum)
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login again.',
            ], 401);
        }

        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        // Create a new token
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        // Calculate expiration timestamp (30 days from now)
        $expiresAt = now()->addMinutes(config('sanctum.expiration', 43200));

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toIso8601String(),
                'expires_in_seconds' => config('sanctum.expiration', 43200) * 60,
            ]
        ], 200);
    }
}
