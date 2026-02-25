<?php

namespace App\Filament\Resources\WorkDaysCalendars\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkDaysCalendarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('month')
                    ->required()
                    ->numeric(),
                TextInput::make('year')
                    ->required(),
                TextInput::make('work_days')
                    ->required()
                    ->numeric(),
            ]);
    }
}
