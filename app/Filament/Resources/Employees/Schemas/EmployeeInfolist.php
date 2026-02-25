<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('رقم الموظف'),
                TextEntry::make('name')
                    ->label('الاسم'),
                TextEntry::make('employeeType.label')
                    ->label('نوع الموظف'),
                TextEntry::make('status.statusName')
                    ->label('الحالة'),
                TextEntry::make('username')
                    ->label('اسم المستخدم')
                    ->placeholder('-'),
                TextEntry::make('salaryType.name')
                    ->label('نوع الراتب')
                    ->placeholder('-'),
                TextEntry::make('phone_number')
                    ->label('رقم الهاتف')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('البريد الإلكتروني')
                    ->placeholder('-'),
            ]);
    }
}
