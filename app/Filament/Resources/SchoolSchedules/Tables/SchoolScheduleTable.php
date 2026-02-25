<?php

namespace App\Filament\Resources\SchoolSchedules\Tables;

use App\Models\SchoolSchedule;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolScheduleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(SchoolSchedule::with([
                'teacherClass.teacher',
                'teacherClass.subject',
                'teacherClass.classModel.grade',
                'teacherClass.classModel.section',
                'day',
                'lessonTime'
            ]))
            ->columns([
                TextColumn::make('day.day_name_ar')
                    ->label('اليوم')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('lessonTime.period_number')
                    ->label('رقم الحصة')
                    ->formatStateUsing(function ($state, SchoolSchedule $record) {
                        if ($record->lessonTime->is_break) {
                            return 'استراحة';
                        }
                        return 'الحصة ' . $state;
                    })
                    ->sortable(),

                TextColumn::make('lessonTime.start_time')
                    ->label('من')
                    ->sortable(),

                TextColumn::make('lessonTime.end_time')
                    ->label('إلى')
                    ->sortable(),

                TextColumn::make('teacherClass.teacher.name')
                    ->label('اسم المعلم')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('teacherClass.subject.name')
                    ->label('المادة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('teacherClass.classModel.grade.name')
                    ->label('الصف')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('teacherClass.classModel.section.name')
                    ->label('القسم')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('day.day_order', 'asc')
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
