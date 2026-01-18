<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\OrganizationResource;
use App\Services\SubscriptionService;
use App\Mail\EmailVerification;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Organization;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}
    /**
     * Register a new user and create organization if needed
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'organization_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
        ]);

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
                \Log::error('Failed to create Pro trial subscription during registration', [
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with registration even if subscription creation fails
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

        // Auto-create user profile with registration data
        $existingProfile = UserProfile::where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();
        
        if (!$existingProfile) {
            // Use first_name and last_name from request, or split from name
            $firstName = $request->first_name;
            $lastName = $request->last_name;
            
            if (!$firstName || !$lastName) {
                $nameParts = explode(' ', $user->name, 2);
                $firstName = $firstName ?? $nameParts[0] ?? '';
                $lastName = $lastName ?? ($nameParts[1] ?? '');
            }
            
            UserProfile::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email_address' => $user->email,
                'phone' => $request->mobile ?? '',
                'address' => '',  // Required field
                'company' => '',  // Required field
            ]);
        }

        // Generate email verification token and send verification email
        try {
            $verificationToken = $user->generateEmailVerificationToken();
            $appUrl = config('app.url');
            $verificationUrl = $appUrl . '/api/email/verify/' . $verificationToken;

            $emailVerification = new EmailVerification($user, $verificationUrl);
            $emailVerification->send();

            \Log::info('Verification email sent during registration', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email during registration', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            // Continue with registration even if email sending fails
        }

        // Don't create token yet - user must verify email first
        // Return response indicating email verification is required
        return response()->json([
            'data' => new UserResource($user),
            'meta' => [
                'organization' => new OrganizationResource($organization),
            ],
            'message' => 'Registration successful! Please check your email to verify your account before logging in.',
            'requires_verification' => true,
        ], 201);
    }
}
