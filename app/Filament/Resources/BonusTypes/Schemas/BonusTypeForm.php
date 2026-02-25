<?php

namespace App\Filament\Resources\BonusTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BonusTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bonus_type_name')
                    ->label('اسم نوع العلاوة')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
