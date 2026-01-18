<?php

namespace App\Repositories;

use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationRepository
{
    public function create(array $data): Organization
    {
        // Generate slug if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name'] . '-' . time());
        }

        // Ensure slug is unique
        $baseSlug = $data['slug'];
        $counter = 1;
        while (Organization::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        return Organization::create($data);
    }

    public function update(Organization $organization, array $data): Organization
    {
        $organization->update($data);
        return $organization->fresh();
    }

    public function findById(int $id): ?Organization
    {
        return Organization::find($id);
    }

    public function findBySlug(string $slug): ?Organization
    {
        return Organization::where('slug', $slug)->first();
    }
}

