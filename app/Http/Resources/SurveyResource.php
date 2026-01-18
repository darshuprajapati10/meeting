<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'survey_name' => $this->survey_name,
            'description' => $this->description,
            'status' => $this->status,
            'response_count' => (int) (isset($this->response_count) && $this->response_count > 0 
                ? $this->response_count 
                : $this->getResponseCount()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'survey_steps' => $this->when(
                $this->relationLoaded('surveySteps'),
                function () {
                    return $this->surveySteps->map(function ($step) {
                        $fields = [];
                        if ($step->relationLoaded('surveyFields')) {
                            $fields = $step->surveyFields->map(function ($field) {
                                return [
                                    'id' => $field->id,
                                    'organization_id' => $field->organization_id,
                                    'survey_id' => $field->survey_id,
                                    'name' => $field->name,
                                    'type' => $field->type,
                                    'description' => $field->description,
                                    'is_required' => $field->is_required,
                                    'options' => $field->options ?? [],
                                ];
                            })->values()->all();
                        }
                        
                        return [
                            'id' => $step->id,
                            'survey_id' => $step->survey_id,
                            'step' => $step->step,
                            'tagline' => $step->tagline,
                            'order' => $step->order,
                            'survey_fields' => $fields,
                        ];
                    })->values()->all();
                },
                []
            ),
        ];
    }
}
