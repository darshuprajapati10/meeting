<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyStep;
use Illuminate\Database\Seeder;

class SurveyStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surveys = Survey::all();
        
        if ($surveys->isEmpty()) {
            $this->command->warn('No surveys found. Please run SurveySeeder first.');
            return;
        }

        $taglines = [
            'Introduction',
            'Basic Information',
            'Detailed Questions',
            'Feedback Section',
            'Rating & Review',
            'Additional Comments',
            'Final Thoughts',
            'Contact Information',
        ];

        // Create 20 survey steps (distributed across surveys)
        $stepCount = 0;
        foreach ($surveys as $survey) {
            // Each survey gets 1-3 steps
            $numSteps = fake()->numberBetween(1, 3);
            
            for ($j = 1; $j <= $numSteps && $stepCount < 20; $j++) {
                SurveyStep::create([
                    'survey_id' => $survey->id,
                    'step' => 'Step ' . $j,
                    'tagline' => fake()->randomElement($taglines),
                    'order' => $j,
                ]);
                $stepCount++;
            }
            
            if ($stepCount >= 20) {
                break;
            }
        }

        $this->command->info('Created 20 survey step records!');
    }
}


