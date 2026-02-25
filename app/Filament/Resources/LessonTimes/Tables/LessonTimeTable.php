<?php

namespace App\Filament\Resources\LessonTimes\Tables;

use App\Models\LessonTime;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class LessonTimeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(LessonTime::query())
            ->columns([
                TextColumn::make('period_number')
                    ->label('رقم الحصة')
                    ->formatStateUsing(fn ($state) => 'الحصة ' . $state)
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('lessonType.name')
                    ->label('نوع الحصة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('start_time')
                    ->label('من')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('إلى')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('المدة')
                    ->formatStateUsing(function (LessonTime $record) {
                        $start = \Carbon\Carbon::parse($record->start_time);
                        $end = \Carbon\Carbon::parse($record->end_time);
                        $diff = $start->diffInMinutes($end);
                        return $diff . ' دقيقة';
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('schoolSchedules_count')
                    ->label('عدد الجداول')
                    ->counts('schoolSchedules')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('period_number', 'asc')
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
