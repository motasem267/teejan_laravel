<?php

namespace App\Filament\Resources\WeekResults\Pages;

use App\Filament\Resources\WeekResults\WeekResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeekResult extends EditRecord
{
    protected static string $resource = WeekResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
