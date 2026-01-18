<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an organization
        $organization = Organization::first();
        
        if (!$organization) {
            $organization = Organization::create([
                'name' => 'Test Organization',
                'slug' => 'test-organization',
                'description' => 'Organization for testing contacts',
                'status' => 'active',
            ]);
        }

        // Get or create a user to be the creator
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Attach user to organization if not already attached
        if (!$user->organizations()->where('organization_id', $organization->id)->exists()) {
            $user->organizations()->attach($organization->id, ['role' => 'admin']);
        }

        // Available groups for random selection
        $groups = ['Clients', 'Partners', 'Team', 'Family', 'Prospects', 'Vendors', 'Friends', 'Colleagues'];
        
        // Job titles for variety
        $jobTitles = [
            'Software Engineer', 'Product Manager', 'Sales Representative', 'Marketing Manager',
            'Accountant', 'HR Manager', 'CEO', 'CTO', 'Designer', 'Developer', 'Consultant',
            'Business Analyst', 'Project Manager', 'Operations Manager', 'Customer Support'
        ];

        // Create 500 fake contacts
        for ($i = 1; $i <= 500; $i++) {
            // Randomly select 0-3 groups
            $selectedGroups = fake()->randomElements($groups, fake()->numberBetween(0, 3));
            
            // Randomly decide if this contact has a referrer (from previously created contacts)
            $referrerId = null;
            if ($i > 1 && fake()->boolean(30)) { // 30% chance of having a referrer
                $referrerId = fake()->numberBetween(1, $i - 1);
            }

            Contact::create([
                'organization_id' => $organization->id,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->boolean(80) ? fake()->phoneNumber() : null, // 80% have phone
                'company' => fake()->boolean(70) ? fake()->company() : null, // 70% have company
                'job_title' => fake()->boolean(60) ? fake()->randomElement($jobTitles) : null, // 60% have job title
                'referrer_id' => $referrerId,
                'groups' => !empty($selectedGroups) ? $selectedGroups : null,
                'address' => fake()->boolean(50) ? fake()->address() : null, // 50% have address
                'notes' => fake()->boolean(40) ? fake()->sentence(10) : null, // 40% have notes
                'created_by' => $user->id,
            ]);

            // Small delay to ensure unique timestamps
            usleep(1000);
        }

        $this->command->info('Created 500 fake contact records!');
    }
}

