<?php

namespace App\Filament\Resources\WeekResults\Pages;

use App\Filament\Resources\WeekResults\WeekResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeekResults extends ListRecords
{
    protected static string $resource = WeekResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
