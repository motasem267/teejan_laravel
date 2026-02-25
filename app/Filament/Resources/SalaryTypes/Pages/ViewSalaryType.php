<?php

namespace App\Filament\Resources\SalaryTypes\Pages;

use App\Filament\Resources\SalaryTypes\SalaryTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSalaryType extends ViewRecord
{
    protected static string $resource = SalaryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
