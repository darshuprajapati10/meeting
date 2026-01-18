<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'survey_id',
        'survey_step_id',
        'survey_field_id',
        'user_id',
        'value',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function surveyStep(): BelongsTo
    {
        return $this->belongsTo(SurveyStep::class);
    }

    public function surveyField(): BelongsTo
    {
        return $this->belongsTo(SurveyField::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
