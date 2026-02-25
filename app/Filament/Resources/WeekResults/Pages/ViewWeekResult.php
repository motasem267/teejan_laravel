<?php

namespace App\Filament\Resources\WeekResults\Pages;

use App\Filament\Resources\WeekResults\WeekResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWeekResult extends ViewRecord
{
    protected static string $resource = WeekResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
