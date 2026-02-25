<?php

namespace App\Filament\Resources\WeekResults\Schemas;

use App\Models\WeekResult;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class WeekResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('week')
                    ->label('الأسبوع')
                    ->options(WeekResult::getWeekOptions())
                    ->required()
                    ->searchable(),
                Select::make('month')
                    ->label('الشهر')
                    ->options(WeekResult::getMonthOptions())
                    ->required()
                    ->searchable(),
                Select::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->relationship('academicYear', 'year_label')
                    ->default(\App\Models\academic_years::getActiveId())
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }
}
