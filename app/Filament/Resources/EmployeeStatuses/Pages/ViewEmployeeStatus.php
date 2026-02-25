<?php

namespace App\Filament\Resources\EmployeeStatuses\Pages;

use App\Filament\Resources\EmployeeStatuses\EmployeeStatusResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeStatus extends ViewRecord
{
    protected static string $resource = EmployeeStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
