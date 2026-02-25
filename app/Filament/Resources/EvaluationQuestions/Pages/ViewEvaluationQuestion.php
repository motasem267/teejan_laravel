<?php

namespace App\Filament\Resources\EvaluationQuestions\Pages;

use App\Filament\Resources\EvaluationQuestions\EvaluationQuestionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEvaluationQuestion extends ViewRecord
{
    protected static string $resource = EvaluationQuestionResource::class;

    protected static ?string $title = 'عرض سؤال التقييم';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
