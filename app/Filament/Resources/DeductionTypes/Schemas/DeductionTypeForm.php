<?php

namespace App\Filament\Resources\DeductionTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeductionTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('deduction_type_name')
                    ->label('اسم نوع الخصم')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
