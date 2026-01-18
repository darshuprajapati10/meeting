<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
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
            'meeting_title' => $this->meeting_title,
            'status' => $this->status,
            'date' => $this->date->format('Y-m-d'),
            'time' => is_string($this->time) ? $this->time : $this->time->format('H:i'),
            'duration' => $this->duration,
            'meeting_type' => $this->meeting_type,
            'custom_location' => $this->custom_location,
            'survey_id' => $this->survey_id,
            'survey' => $this->when(
                $this->relationLoaded('survey') && $this->survey !== null,
                function () {
                    return [
                        'id' => $this->survey->id,
                        'survey_name' => $this->survey->survey_name,
                        'survey_steps' => $this->when(
                            $this->survey->relationLoaded('surveySteps'),
                            function () {
                                return $this->survey->surveySteps->map(function ($step) {
                                    $fields = [];
                                    if ($step->relationLoaded('surveyFields')) {
                                        $fields = $step->surveyFields->map(function ($field) {
                                            return [
                                                'id' => $field->id,
                                                'name' => $field->name,
                                                'type' => $field->type,
                                                'description' => $field->description,
                                                'is_required' => $field->is_required,
                                                'options' => $field->options ?? [],
                                                'order' => $field->order,
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
            ),
            'agenda_notes' => $this->agenda_notes,
            'created_by' => $this->created_by,
            'attendees' => $this->when(
                $this->relationLoaded('attendees'),
                function () {
                    return $this->attendees->map(function ($contact) {
                        return [
                            'id' => $contact->id,
                            'first_name' => $contact->first_name,
                            'last_name' => $contact->last_name,
                            'email' => $contact->email,
                            'phone' => $contact->phone,
                        ];
                    })->values()->all();
                },
                []
            ),
            'notifications' => $this->when(
                $this->relationLoaded('notifications'),
                function () {
                    return $this->notifications->map(function ($notification) {
                        return [
                            'id' => $notification->id,
                            'minutes' => $notification->minutes,
                            'unit' => $notification->unit,
                            'trigger' => $notification->trigger,
                            'is_enabled' => $notification->is_enabled,
                        ];
                    })->values()->all();
                },
                []
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'has_submitted_survey' => $this->when(
                $request->user(),
                function () use ($request) {
                    return $this->hasUserSubmittedSurvey($request->user()->id);
                },
                false
            ),
            'has_submitted' => $this->when(
                $request->user(),
                function () use ($request) {
                    return $this->hasUserSubmittedSurvey($request->user()->id);
                },
                false
            ),
            'survey_submitted_at' => $this->when(
                $request->user() && $this->survey_id,
                function () use ($request) {
                    $submission = \App\Models\SurveySubmission::where('user_id', $request->user()->id)
                        ->where('meeting_id', $this->id)
                        ->where('survey_id', $this->survey_id)
                        ->first();
                    
                    if ($submission && $submission->submitted_at) {
                        return $submission->submitted_at->toIso8601String();
                    }
                    
                    // Fallback to survey_responses for backward compatibility
                    $response = \App\Models\SurveyResponse::where('user_id', $request->user()->id)
                        ->where('meeting_id', $this->id)
                        ->where('survey_id', $this->survey_id)
                        ->first();
                    
                    if ($response) {
                        return ($response->submitted_at ?? $response->created_at)?->toIso8601String();
                    }
                    
                    return null;
                },
                null
            ),
        ];
    }
}

