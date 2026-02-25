<?php

namespace App\Filament\Resources\Marks\Schemas;

use App\Models\academic_years;
use App\Models\AcademicPeriod;
use App\Models\GradeSubject;
use App\Models\StudentEnrollment;
use App\Models\SubjectFullMark;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                Select::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->options(academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                    ->default(academic_years::getActiveId())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->dehydrated(false)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('student_inrollment_id', null);
                        $set('subject_id', null);
                        $set('full_mark', null);
                    })
                    ->helperText('تحديد السنة الدراسية يُفلتر قائمة الطلاب'),

                Select::make('student_inrollment_id')
                    ->label('الطالب')
                    ->options(function (callable $get): array {
                        $yearId = $get('academic_year_id');
                        if (!$yearId) {
                            return [];
                        }
                        return StudentEnrollment::with(['student', 'grade'])
                            ->where('academic_year_id', $yearId)
                            ->get()
                            ->mapWithKeys(fn ($e) => [
                                $e->id => ($e->student?->full_name ?? '-')
                                    . ' (' . ($e->grade?->name ?? '') . ')',
                            ])
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('subject_id', null);
                        $set('full_mark', null);
                    })
                    ->disabled(fn (callable $get) => !$get('academic_year_id'))
                    ->helperText('يعرض فقط الطلاب المقيَّدين في السنة الدراسية المختارة'),

                Select::make('subject_id')
                    ->label('المادة')
                    ->options(function (callable $get): array {
                        $enrollmentId = $get('student_inrollment_id');
                        if (!$enrollmentId) {
                            return [];
                        }
                        $enrollment = StudentEnrollment::find($enrollmentId);
                        if (!$enrollment?->grade_id) {
                            return [];
                        }
                        return GradeSubject::with('subject')
                            ->where('gradeID', $enrollment->grade_id)
                            ->get()
                            ->mapWithKeys(fn ($gs) => [
                                $gs->subjectID => $gs->subject?->name ?? '-',
                            ])
                            ->filter()
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $periodId = $get('AcademicPeriodID');
                        if ($state && $periodId) {
                            $fm = SubjectFullMark::where('subjectId', $state)
                                ->where('academicperiodID', $periodId)
                                ->value('FullMark');
                            $set('full_mark', $fm);
                        } else {
                            $set('full_mark', null);
                        }
                    })
                    ->disabled(fn (callable $get) => !$get('student_inrollment_id'))
                    ->helperText('فقط مواد الصف الدراسي لهذا الطالب'),

                Select::make('AcademicPeriodID')
                    ->label('الفترة الدراسية')
                    ->options(AcademicPeriod::orderBy('AcademicPeriodID')->pluck('PeriodName', 'AcademicPeriodID'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $subjectId = $get('subject_id');
                        if ($state && $subjectId) {
                            $fm = SubjectFullMark::where('subjectId', $subjectId)
                                ->where('academicperiodID', $state)
                                ->value('FullMark');
                            $set('full_mark', $fm);
                        } else {
                            $set('full_mark', null);
                        }
                    }),

                TextInput::make('full_mark')
                    ->label('الدرجة الكبرى')
                    ->numeric()
                    ->readOnly()
                    ->placeholder('تُحدَّد تلقائياً حسب المادة والفترة'),

                TextInput::make('student_mark')
                    ->label('درجة الطالب')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }
}