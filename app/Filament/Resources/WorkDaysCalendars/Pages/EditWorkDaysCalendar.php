<?php

namespace App\Filament\Resources\WorkDaysCalendars\Pages;

use App\Filament\Resources\WorkDaysCalendars\WorkDaysCalendarResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkDaysCalendar extends EditRecord
{
    protected static string $resource = WorkDaysCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
