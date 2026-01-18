<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Handle attendees field
        if ($this->has('attendees')) {
            $attendees = $this->attendees;
            
            // If null or string "null", convert to empty array
            if ($attendees === null || $attendees === 'null') {
                $this->merge(['attendees' => []]);
            }
            // If it's a JSON string, decode it
            elseif (is_string($attendees)) {
                $decoded = json_decode($attendees, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->merge(['attendees' => $decoded]);
                } else {
                    // If not valid JSON, try to parse as comma-separated values
                    $this->merge(['attendees' => []]);
                }
            }
            // If it's not an array, convert to array
            elseif (!is_array($attendees)) {
                $this->merge(['attendees' => []]);
            }
        }

        // Handle notifications field
        if ($this->has('notifications')) {
            $notifications = $this->notifications;
            
            // If null or string "null", convert to empty array
            if ($notifications === null || $notifications === 'null') {
                $this->merge(['notifications' => []]);
            }
            // If it's a JSON string, decode it
            elseif (is_string($notifications)) {
                $decoded = json_decode($notifications, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->merge(['notifications' => $decoded]);
                } else {
                    // If not valid JSON, convert to empty array
                    $this->merge(['notifications' => []]);
                }
            }
            // If it's not an array, convert to array
            elseif (!is_array($notifications)) {
                $this->merge(['notifications' => []]);
            }
            // If it's an array, check if it's a simple array of integers (e.g., [10, 15])
            elseif (is_array($notifications) && !empty($notifications)) {
                // Check if first element is an integer (simple format) and not already an object
                $firstElement = reset($notifications);
                if (is_numeric($firstElement) && !is_array($firstElement) && !is_object($firstElement)) {
                    // Convert simple format [10, 15] to full format with deduplication
                    $convertedNotifications = [];
                    $seenMinutes = []; // Track seen minutes for deduplication
                    
                    foreach ($notifications as $minutes) {
                        // Only process positive integers (accepts both int and string numbers)
                        $minutesInt = (int)$minutes;
                        if (is_numeric($minutes) && $minutesInt > 0 && $minutesInt == $minutes) {
                            // Deduplicate: only add if not seen before
                            if (!in_array($minutesInt, $seenMinutes)) {
                                $seenMinutes[] = $minutesInt;
                                $convertedNotifications[] = [
                                    'minutes' => $minutesInt,
                                    'unit' => 'minutes',
                                    'trigger' => 'before',
                                    'is_enabled' => true,
                                ];
                            }
                        }
                    }
                    // Only merge if we have valid notifications
                    if (!empty($convertedNotifications)) {
                        $this->merge(['notifications' => $convertedNotifications]);
                    } else {
                        // If no valid notifications, set to empty array
                        $this->merge(['notifications' => []]);
                    }
                }
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:meetings,id'],
            'meeting_title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:Created,Scheduled,Completed,Cancelled,Pending,Rescheduled'],
            'date' => ['required', 'date'],
            
            'time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', 'in:15,30,45,60,90,120'],
            'meeting_type' => ['required', 'in:Video Call,In-Person Meeting,Phone Call,Online Meeting'],
            'custom_location' => ['nullable', 'string', 'max:500'],
            'survey_id' => ['nullable', 'integer', 'exists:surveys,id'],
            'agenda_notes' => ['nullable', 'string'],
            'attendees' => ['required', 'array'],
            'attendees.*' => ['required_with:attendees', 'integer', 'exists:contacts,id'],
            'notifications' => ['nullable', 'array', 'max:10'], // Maximum 10 reminders per meeting
            // After prepareForValidation, all notifications should be in full format
            // So we validate the full format structure
            'notifications.*.minutes' => ['required_with:notifications', 'integer', 'min:1'],
            'notifications.*.unit' => ['required_with:notifications', 'in:minutes,hours,days'],
            'notifications.*.trigger' => ['required_with:notifications', 'in:before,after'],
            'notifications.*.is_enabled' => ['nullable', 'boolean'],
        ];
    }
}

