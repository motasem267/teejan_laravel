<?php

namespace App\Filament\Resources\EvaluationTypes\Pages;

use App\Filament\Resources\EvaluationTypes\EvaluationTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEvaluationType extends EditRecord
{
    protected static string $resource = EvaluationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
