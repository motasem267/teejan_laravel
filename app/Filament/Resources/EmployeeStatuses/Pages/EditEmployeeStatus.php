<?php

namespace App\Filament\Resources\EmployeeStatuses\Pages;

use App\Filament\Resources\EmployeeStatuses\EmployeeStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeStatus extends EditRecord
{
    protected static string $resource = EmployeeStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
