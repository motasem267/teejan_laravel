<?php

namespace App\Filament\Resources\EmployeeEnrollments\Pages;

use App\Filament\Resources\EmployeeEnrollments\EmployeeEnrollmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeEnrollment extends EditRecord
{
    protected static string $resource = EmployeeEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
