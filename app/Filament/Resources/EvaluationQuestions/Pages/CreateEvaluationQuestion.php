<?php

namespace App\Filament\Resources\EvaluationQuestions\Pages;

use App\Filament\Resources\EvaluationQuestions\EvaluationQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvaluationQuestion extends CreateRecord
{
    protected static string $resource = EvaluationQuestionResource::class;

    protected static ?string $title = 'إضافة سؤال تقييم جديد';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
