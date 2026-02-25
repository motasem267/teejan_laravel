<?php

namespace App\Filament\Resources\EmployeeTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type_name')
                    ->label('نوع الموظف')
                    ->required(),
            ]);
    }
}
