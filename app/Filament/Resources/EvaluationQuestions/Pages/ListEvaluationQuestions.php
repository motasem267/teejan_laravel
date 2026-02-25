<?php

namespace App\Filament\Resources\EvaluationQuestions\Pages;

use App\Filament\Resources\EvaluationQuestions\EvaluationQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEvaluationQuestions extends ListRecords
{
    protected static string $resource = EvaluationQuestionResource::class;

    protected static ?string $title = 'اسئلة التقييم';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
