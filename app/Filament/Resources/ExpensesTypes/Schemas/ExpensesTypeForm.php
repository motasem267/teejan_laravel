<?php

namespace App\Filament\Resources\ExpensesTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExpensesTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('expenses_type_name')
                    ->label('اسم نوع المصروف')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
