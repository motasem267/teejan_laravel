<?php

namespace App\Filament\Resources\SchoolSchedules\Pages;

use App\Filament\Resources\SchoolSchedules\SchoolScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolSchedules extends ListRecords
{
    protected static string $resource = SchoolScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
