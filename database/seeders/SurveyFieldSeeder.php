<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Survey;
use App\Models\SurveyField;
use App\Models\SurveyStep;
use Illuminate\Database\Seeder;

class SurveyFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surveySteps = SurveyStep::with('survey')->get();
        $organizations = Organization::all();
        
        if ($surveySteps->isEmpty() || $organizations->isEmpty()) {
            $this->command->warn('No survey steps or organizations found. Please run SurveyStepSeeder first.');
            return;
        }

        $fieldTypes = ['Short Answer', 'Paragraph', 'Multiple Choice', 'Checkboxes', 'Dropdown', 'Rating Scale', 'Email', 'Number', 'Date', 'File Upload'];
        $fieldNames = [
            'What is your name?',
            'Please provide your email',
            'Rate your experience',
            'How satisfied are you?',
            'Any additional comments?',
            'What is your age?',
            'Select your preference',
            'When did you last visit?',
            'What is your phone number?',
            'Describe your experience',
        ];

        // Create 20 survey fields (distributed across steps)
        $fieldCount = 0;
        foreach ($surveySteps as $step) {
            // Each step gets 1-2 fields
            $numFields = fake()->numberBetween(1, 2);
            
            for ($j = 1; $j <= $numFields && $fieldCount < 20; $j++) {
                $fieldType = fake()->randomElement($fieldTypes);
                $options = null;
                
                // Add options for choice-based field types
                if (in_array($fieldType, ['Multiple Choice', 'Checkboxes', 'Dropdown', 'Rating Scale'])) {
                    $options = [1, 2, 3, 4, 5];
                }
                
                SurveyField::create([
                    'organization_id' => $step->survey->organization_id,
                    'survey_id' => $step->survey_id,
                    'survey_step_id' => $step->id,
                    'name' => fake()->randomElement($fieldNames),
                    'type' => $fieldType,
                    'description' => fake()->sentence(8),
                    'is_required' => fake()->boolean(70), // 70% are required
                    'options' => $options,
                    'order' => $j,
                ]);
                $fieldCount++;
            }
            
            if ($fieldCount >= 20) {
                break;
            }
        }

        $this->command->info('Created 20 survey field records!');
    }
}

