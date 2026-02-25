<?php

namespace App\Filament\Resources\InstallmentTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InstallmentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('installment_type_name')
                    ->label('اسم نوع القسط')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
