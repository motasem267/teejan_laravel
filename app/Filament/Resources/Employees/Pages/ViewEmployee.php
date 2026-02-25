<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Pages\ChangePassword;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;


class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('change_password')
                ->label('تغيير كلمة المرور')
                ->url(fn () => ChangePassword::getUrl('index') . '?employee_id=' . $this->record->id)
                ->visible(fn () => Auth::check() && (Auth::id() === $this->record->id || (method_exists(Auth::user(), 'hasPermission') && Auth::user()->hasPermission('employees.change-password')))),
        ];
    }
}
