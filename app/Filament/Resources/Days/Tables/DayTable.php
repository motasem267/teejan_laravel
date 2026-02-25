<?php

namespace App\Filament\Resources\Days\Tables;

use App\Models\Day;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DayTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Day::query())
            ->columns([
                TextColumn::make('day_order')
                    ->label('الترتيب')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('day_name_ar')
                    ->label('اسم اليوم')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('day_order', 'asc')
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
