<?php

namespace App\Repositories;

use App\Models\UserProfile;
use App\Models\Organization;

class UserProfileRepository
{
    public function findById(int $id): ?UserProfile
    {
        return UserProfile::find($id);
    }

    public function create(array $data): UserProfile
    {
        $userProfile = UserProfile::create($data);
        // Load user relationship to get login data
        return $userProfile->load('user');
    }

    public function update(UserProfile $userProfile, array $data): UserProfile
    {
        $userProfile->update($data);
        // Load user relationship to get login data
        return $userProfile->fresh(['user']);
    }

    public function delete(UserProfile $userProfile): bool
    {
        return $userProfile->delete();
    }

    public function findByOrganizationAndUser(int $organizationId, int $userId): ?UserProfile
    {
        return UserProfile::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->first();
    }

    public function getByOrganization(int $organizationId, int $perPage = 15, int $page = 1)
    {
        return UserProfile::where('organization_id', $organizationId)
            ->with('user') // Load user relationship to get login data
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}

