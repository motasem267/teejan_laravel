<?php

namespace App\Filament\Resources\EvaluationAnswers\Pages;

use App\Filament\Resources\EvaluationAnswers\EvaluationAnswerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEvaluationAnswer extends ViewRecord
{
    protected static string $resource = EvaluationAnswerResource::class;

    protected static ?string $title = 'عرض إجابة التقييم';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
