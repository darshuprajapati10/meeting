<?php

namespace App\Repositories;

use App\Models\SurveyStep;
use App\Models\Survey;

class SurveyStepRepository
{
    public function findById(int $id): ?SurveyStep
    {
        return SurveyStep::find($id);
    }

    public function create(array $data): SurveyStep
    {
        return SurveyStep::create($data);
    }

    public function update(SurveyStep $surveyStep, array $data): SurveyStep
    {
        $surveyStep->update($data);
        return $surveyStep->fresh();
    }

    public function delete(SurveyStep $surveyStep): bool
    {
        return $surveyStep->delete();
    }

    public function getBySurvey(int $surveyId, int $perPage = 15, int $page = 1)
    {
        return SurveyStep::where('survey_id', $surveyId)
            ->orderBy('order')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getBySurveyAll(int $surveyId)
    {
        return SurveyStep::where('survey_id', $surveyId)
            ->orderBy('order')
            ->with('surveyFields')
            ->get();
    }
}

