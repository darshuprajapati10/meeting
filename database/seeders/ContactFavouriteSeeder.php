<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactFavourite;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactFavouriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = Contact::all();
        $users = User::all();
        $organizations = Organization::all();
        
        if ($contacts->isEmpty() || $users->isEmpty() || $organizations->isEmpty()) {
            $this->command->warn('No contacts, users, or organizations found. Please run ContactSeeder first.');
            return;
        }

        // Create 20 contact favourites
        $favouriteCount = 0;
        $usedCombinations = [];
        
        while ($favouriteCount < 20) {
            $contact = $contacts->random();
            $user = $users->random();
            $organization = $organizations->random();
            
            // Ensure contact belongs to organization
            if ($contact->organization_id !== $organization->id) {
                continue;
            }
            
            // Create unique combination key
            $key = $user->id . '_' . $contact->id . '_' . $organization->id;
            
            // Skip if this combination already exists
            if (in_array($key, $usedCombinations)) {
                continue;
            }
            
            ContactFavourite::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'contact_id' => $contact->id,
                'is_favourite' => true,
            ]);
            
            $usedCombinations[] = $key;
            $favouriteCount++;
        }

        $this->command->info('Created 20 contact favourite records!');
    }
}


