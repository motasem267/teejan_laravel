<?php

namespace App\Filament\Resources\EvaluationAnswers\Pages;

use App\Filament\Resources\EvaluationAnswers\EvaluationAnswerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEvaluationAnswer extends EditRecord
{
    protected static string $resource = EvaluationAnswerResource::class;

    protected static ?string $title = 'تعديل إجابة التقييم';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
