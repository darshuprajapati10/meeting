<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();
        $users = User::all();
        
        if ($organizations->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No organizations or users found. Please run OrganizationSeeder and UserSeeder first.');
            return;
        }

        $statuses = ['Draft', 'Active', 'Archived'];
        $surveyTypes = [
            'Customer Satisfaction Survey',
            'Employee Feedback Survey',
            'Product Feedback Survey',
            'Market Research Survey',
            'Event Feedback Survey',
            'Training Evaluation Survey',
            'Health & Wellness Survey',
            'Brand Awareness Survey',
            'Service Quality Survey',
            'User Experience Survey',
        ];

        // Create 20 surveys
        for ($i = 1; $i <= 20; $i++) {
            $organization = $organizations->random();
            $user = $users->random();
            $surveyName = fake()->randomElement($surveyTypes) . ' ' . $i;
            
            Survey::create([
                'organization_id' => $organization->id,
                'survey_name' => $surveyName,
                'description' => fake()->sentence(15),
                'status' => fake()->randomElement($statuses),
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('Created 20 survey records!');
    }
}


