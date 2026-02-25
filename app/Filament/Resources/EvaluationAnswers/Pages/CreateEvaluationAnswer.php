<?php

namespace App\Filament\Resources\EvaluationAnswers\Pages;

use App\Filament\Resources\EvaluationAnswers\EvaluationAnswerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvaluationAnswer extends CreateRecord
{
    protected static string $resource = EvaluationAnswerResource::class;

    protected static ?string $title = 'إضافة إجابة تقييم جديدة';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
