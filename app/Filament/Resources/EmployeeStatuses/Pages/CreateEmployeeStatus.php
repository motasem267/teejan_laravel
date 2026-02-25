<?php

namespace App\Filament\Resources\EmployeeStatuses\Pages;

use App\Filament\Resources\EmployeeStatuses\EmployeeStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeStatus extends CreateRecord
{
    protected static string $resource = EmployeeStatusResource::class;
}
