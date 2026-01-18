<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Meeting;
use App\Models\Contact;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    public function createFromGoogle(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_verified_at' => now(), // Google emails are verified
            'password' => Hash::make(Str::random(32)), // Random password since using Google
            'google_id' => $data['google_id'] ?? null,
        ]);
    }

    public function update(User $user, array $data): User
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function getProfileStats(User $user): array
    {
        $organization = $user->organizations()->first();
        $organizationId = $organization ? $organization->id : null;

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Meetings this month (scheduled in current month, in user's organization)
        $meetingsThisMonth = 0;
        if ($organizationId) {
            $meetingsThisMonth = Meeting::where('organization_id', $organizationId)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->where('status', '!=', 'Cancelled')
                ->count();
        }

        // Total contacts in user's organization
        $totalContacts = 0;
        if ($organizationId) {
            $totalContacts = Contact::where('organization_id', $organizationId)->count();
        }

        // Hours scheduled (sum of all meeting durations in minutes, convert to hours)
        $hoursScheduled = 0;
        if ($organizationId) {
            $totalMinutes = Meeting::where('organization_id', $organizationId)
                ->where('status', '!=', 'Cancelled')
                ->sum('duration');
            $hoursScheduled = round($totalMinutes / 60, 1);
        }

        // Meeting rating (default to 4.8, can be enhanced later with actual rating system)
        $meetingRating = 4.8;

        return [
            'meetings_this_month' => $meetingsThisMonth,
            'total_contacts' => $totalContacts,
            'hours_scheduled' => $hoursScheduled,
            'meeting_rating' => $meetingRating,
        ];
    }
}

