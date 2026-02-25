<?php

namespace App\Filament\Resources\ClassModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ClassModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('grade_id')
                    ->label('الصف')
                    ->relationship('grade', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('section_id')
                    ->label('السكشن')
                    ->relationship('section', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }
}
