<?php

namespace App\Filament\Resources\EmployeeStatuses\Pages;

use App\Filament\Resources\EmployeeStatuses\EmployeeStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeStatuses extends ListRecords
{
    protected static string $resource = EmployeeStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
