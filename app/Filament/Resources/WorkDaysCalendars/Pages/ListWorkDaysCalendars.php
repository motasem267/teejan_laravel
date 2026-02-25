<?php

namespace App\Filament\Resources\WorkDaysCalendars\Pages;

use App\Filament\Resources\WorkDaysCalendars\WorkDaysCalendarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkDaysCalendars extends ListRecords
{
    protected static string $resource = WorkDaysCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
