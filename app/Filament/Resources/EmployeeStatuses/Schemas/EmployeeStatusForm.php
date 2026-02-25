<?php

namespace App\Filament\Resources\EmployeeStatuses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('status_name')
                    ->label('اسم الحالة الوظيفية')
                    ->required(),
            ]);
    }
}
