<?php

namespace App\Filament\Resources\EvaluationQuestions\Pages;

use App\Filament\Resources\EvaluationQuestions\EvaluationQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEvaluationQuestion extends EditRecord
{
    protected static string $resource = EvaluationQuestionResource::class;

    protected static ?string $title = 'تعديل سؤال التقييم';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
