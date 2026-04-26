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
            ->query(
                SchoolSchedule::query()
                    // join related tables so we can sort by their columns
                    ->select('school_schedules.*')
                    ->with([
                        'teacherClass.teacher',
                        'teacherClass.subject',
                        'teacherClass.classModel.grade',
                        'teacherClass.classModel.section',
                        'day',
                        'lessonTime',
                    ])
                    ->leftJoin('days', 'days.id', '=', 'school_schedules.day_id')
                    ->leftJoin('lesson_times', 'lesson_times.id', '=', 'school_schedules.lesson_time_id')
            )
            ->columns([
                TextColumn::make('day.day_name_ar')
                    ->label('اليوم')
                    // explicitly point sortable to the joined table so the order clause is valid
                    ->sortable('days.day_name_ar')
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
            // default sort should reference the actual table name (days) and we joined it above
            ->defaultSort('days.day_order', 'asc')
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
