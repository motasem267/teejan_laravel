<?php

namespace App\Filament\Resources\Marks\Pages;

use App\Filament\Resources\Marks\MarkResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMark extends ViewRecord
{
    protected static string $resource = MarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
