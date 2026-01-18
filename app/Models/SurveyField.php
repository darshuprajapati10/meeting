<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyField extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'survey_id', 'survey_step_id', 'name', 
        'type', 'description', 'is_required', 'options', 'order'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function surveyStep(): BelongsTo
    {
        return $this->belongsTo(SurveyStep::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
