<?php

namespace App\Filament\Resources\SchoolSchedules\Schemas;

use App\Models\Day;
use App\Models\LessonTime;
use App\Models\TeacherClass;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class SchoolScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('teacher_class_id')
                    ->label('معلم - مادة - فصل - قسم')
                    ->placeholder('اختر معلم وفصل')
                    ->options(function (): Collection {
                        return TeacherClass::with(['teacher', 'subject', 'classModel.grade', 'classModel.section'])
                            ->get()
                            ->mapWithKeys(function (TeacherClass $teacherClass) {
                                $label = sprintf(
                                    '%s - %s - %s - %s',
                                    $teacherClass->teacher->name,
                                    $teacherClass->subject->name,
                                    $teacherClass->classModel->grade->name,
                                    $teacherClass->classModel->section->name
                                );
                                return [$teacherClass->id => $label];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->validationMessages([
                        'required' => 'يجب اختيار معلم وفصل',
                    ]),

                Select::make('day_id')
                    ->label('اليوم')
                    ->placeholder('اختر اليوم')
                    ->options(function (): Collection {
                        return Day::orderBy('day_order', 'asc')
                            ->get()
                            ->mapWithKeys(function (Day $day) {
                                return [$day->id => $day->day_name_ar];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->validationMessages([
                        'required' => 'يجب اختيار اليوم',
                    ]),

                Select::make('lesson_time_id')
                    ->label('الحصة')
                    ->placeholder('اختر الحصة')
                    ->options(function (): Collection {
                        return LessonTime::orderBy('period_number', 'asc')
                            ->get()
                            ->mapWithKeys(function (LessonTime $lessonTime) {
                                if ($lessonTime->is_break) {
                                    $label = sprintf(
                                        'استراحة - %s إلى %s',
                                        $lessonTime->start_time,
                                        $lessonTime->end_time
                                    );
                                } else {
                                    $label = sprintf(
                                        'الحصة %d - %s إلى %s',
                                        $lessonTime->period_number,
                                        $lessonTime->start_time,
                                        $lessonTime->end_time
                                    );
                                }
                                return [$lessonTime->id => $label];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->validationMessages([
                        'required' => 'يجب اختيار الحصة',
                    ]),

                Hidden::make('id'),
            ]);
    }
}
