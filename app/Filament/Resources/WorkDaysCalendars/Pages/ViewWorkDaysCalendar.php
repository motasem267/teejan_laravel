<?php

namespace App\Filament\Resources\WorkDaysCalendars\Pages;

use App\Filament\Resources\WorkDaysCalendars\WorkDaysCalendarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkDaysCalendar extends ViewRecord
{
    protected static string $resource = WorkDaysCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
