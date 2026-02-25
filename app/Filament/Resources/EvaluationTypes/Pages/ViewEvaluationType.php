<?php

namespace App\Filament\Resources\EvaluationTypes\Pages;

use App\Filament\Resources\EvaluationTypes\EvaluationTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEvaluationType extends ViewRecord
{
    protected static string $resource = EvaluationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
