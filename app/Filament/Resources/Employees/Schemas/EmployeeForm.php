<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('رقم الموظف')
                    ->numeric()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->minValue(1),
                TextInput::make('name')
                    ->label('الاسم الكامل')
                    ->required(),
                Select::make('emp_type_id')
                    ->label('نوع الموظف')
                    ->relationship('employeeType', 'type_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('status_id')
                    ->label('الحالة')
                    ->relationship('status', 'status_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('salary')
                    ->label('الراتب')
                    ->numeric()
                    ->default(null),
                Select::make('salary_by')
                    ->label('نوع الراتب')
                    ->relationship('salaryType', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->visible(fn ($context) => $context === 'create'),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->default(null),
                TextInput::make('phone_number')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->regex('/^09\d{8}$/')
                    ->length(10)
                    ->helperText('يجب أن يبدأ بـ 09 ويتكون من 10 أرقام')
                    ->default(null),
            ]);
    }
}
