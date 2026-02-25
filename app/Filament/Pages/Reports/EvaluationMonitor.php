<?php

namespace App\Filament\Pages\Reports;

use App\Models\academic_years;
use App\Models\ActivityLog;
use App\Models\ClassModel;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Section;
use App\Models\student;
use App\Models\StudentEnrollment;
use App\Models\StudentEvaluationRecord;
use App\Models\TeacherClass;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EvaluationMonitor extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'مراقبة تقييمات الطلاب';
    protected static string|\UnitEnum|null $navigationGroup = 'تقارير';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-eye';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.reports.evaluation-monitor';
    protected static ?string $title = 'مراقبة تقييمات الطلاب';

    public $selectedMonth = null;

    public function mount(): void
    {
        // تعيين الشهر الحالي كافتراضي
        $this->selectedMonth = now()->month;
        
        // تسجيل الحدث
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'viewed',
                'description' => 'عرض صفحة مراقبة تقييمات الطلاب',
                'model_type' => StudentEvaluationRecord::class,
                'model_id' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Ignore logging errors
        }
    }

    public function updatedSelectedMonth()
    {
        // تحديث الجدول عند تغير الشهر
        $this->resetTable();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }
        
        return $user->hasPermission('week-results.monitoring');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(TeacherClass::query())
            ->modifyQueryUsing(fn (Builder $query) => $this->applyFiltersToQuery($query))
            ->columns([
                TextColumn::make('teacher.name')
                    ->label('المعلم/ة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('المادة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('classModel.grade.name')
                    ->label('الصف')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('classModel.section.name')
                    ->label('الشعبة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('current_month')
                    ->label('الشهر')
                    ->alignCenter()
                    ->state(function (TeacherClass $record): string {
                        if (!$this->selectedMonth) {
                            return '-';
                        }
                        
                        $months = [
                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                        ];
                        
                        return $months[$this->selectedMonth] ?? 'غير محدد';
                    })
                    ->badge()
                    ->color('info'),

                // أعمدة الأسابيع الأربعة
                TextColumn::make('week1_status')
                    ->label('الأسبوع الأول')
                    ->alignCenter()
                    ->state(fn (TeacherClass $record) => $this->getWeekStatus($record, 1))
                    ->badge()
                    ->color(fn (TeacherClass $record): string => $this->getWeekStatusColor($record, 1)),

                TextColumn::make('week2_status')
                    ->label('الأسبوع الثاني')
                    ->alignCenter()
                    ->state(fn (TeacherClass $record) => $this->getWeekStatus($record, 2))
                    ->badge()
                    ->color(fn (TeacherClass $record): string => $this->getWeekStatusColor($record, 2)),

                TextColumn::make('week3_status')
                    ->label('الأسبوع الثالث')
                    ->alignCenter()
                    ->state(fn (TeacherClass $record) => $this->getWeekStatus($record, 3))
                    ->badge()
                    ->color(fn (TeacherClass $record): string => $this->getWeekStatusColor($record, 3)),

                TextColumn::make('week4_status')
                    ->label('الأسبوع الرابع')
                    ->alignCenter()
                    ->state(fn (TeacherClass $record) => $this->getWeekStatus($record, 4))
                    ->badge()
                    ->color(fn (TeacherClass $record): string => $this->getWeekStatusColor($record, 4)),


            ])
            ->filters([
                SelectFilter::make('teacher_id')
                    ->label('المعلم/ة')
                    ->options(
                        Employee::whereHas('teacherClasses')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable(),

                SelectFilter::make('grade_id')
                    ->label('الصف')
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereHas('classModel', function ($q) use ($data) {
                                $q->where('grade_id', $data['value']);
                            });
                        }
                    })
                    ->options(function (): array {
                        $teacherId = $this->tableFilters['teacher_id']['value'] ?? null;

                        if ($teacherId) {
                            // عرض الصفوف التي يدرس فيها المعلم فقط
                            return Grade::whereHas('classes', function ($q) use ($teacherId) {
                                $q->whereHas('teacherClasses', function ($q2) use ($teacherId) {
                                    $q2->where('teacher_id', $teacherId);
                                });
                            })
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                        }

                        // عرض جميع الصفوف إذا لم يتم اختيار معلم
                        return Grade::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('section_id')
                    ->label('الشعبة')
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->whereHas('classModel', function ($q) use ($data) {
                                $q->where('section_id', $data['value']);
                            });
                        }
                    })
                    ->options(function (): array {
                        $teacherId = $this->tableFilters['teacher_id']['value'] ?? null;
                        $gradeId = $this->tableFilters['grade_id']['value'] ?? null;

                        $query = Section::query();

                        if ($teacherId) {
                            // عرض الشعب التي يدرس فيها المعلم فقط
                            $query->whereHas('classModels', function ($q) use ($teacherId, $gradeId) {
                                $q->whereHas('teacherClasses', function ($q2) use ($teacherId) {
                                    $q2->where('teacher_id', $teacherId);
                                });

                                // إذا تم اختيار صف، فلتر حسب الصف
                                if ($gradeId) {
                                    $q->where('grade_id', $gradeId);
                                }
                            });
                        } elseif ($gradeId) {
                            // إذا تم اختيار صف فقط بدون معلم
                            $query->whereHas('classModels', function ($q) use ($gradeId) {
                                $q->where('grade_id', $gradeId);
                            });
                        }

                        return $query->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->where('subject_id', $data['value']);
                        }
                    })
                    ->options(function (): array {
                        $teacherId = $this->tableFilters['teacher_id']['value'] ?? null;
                        $gradeId = $this->tableFilters['grade_id']['value'] ?? null;
                        $sectionId = $this->tableFilters['section_id']['value'] ?? null;

                        $query = \App\Models\subject::query();
                        
                        if ($teacherId || $gradeId || $sectionId) {
                            $query->whereHas('teacherClasses', function ($q) use ($teacherId, $gradeId, $sectionId) {
                                if ($teacherId) {
                                    $q->where('teacher_id', $teacherId);
                                }
                                if ($gradeId || $sectionId) {
                                    $q->whereHas('classModel', function ($classQ) use ($gradeId, $sectionId) {
                                        if ($gradeId) {
                                            $classQ->where('grade_id', $gradeId);
                                        }
                                        if ($sectionId) {
                                            $classQ->where('section_id', $sectionId);
                                        }
                                    });
                                }
                            });
                        }
                        
                        return $query->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->options(
                        academic_years::orderBy('year_label', 'desc')
                            ->pluck('year_label', 'id')
                    )
                    ->default(academic_years::getActiveId()),
            ])
            ->defaultSort('teacher.name', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function applyFiltersToQuery(Builder $query): Builder
    {
        return $query->with(['teacher', 'subject', 'classModel.grade', 'classModel.section']);
    }

    protected function getTotalStudents(TeacherClass $teacherClass): int
    {
        $class = $teacherClass->classModel;
        if (!$class) return 0;

        $filters = $this->tableFilters;
        $academicYearId = $filters['academic_year_id']['value'] ?? academic_years::getActiveId();

        // قراءة عدد الطلاب من جدول student_enrollments (قيد الطلبة)
        return StudentEnrollment::where('grade_id', $class->grade_id)
            ->where('section_id', $class->section_id)
            ->where('academic_year_id', $academicYearId)
            ->distinct('student_id')
            ->count('student_id');
    }

    protected function getEvaluatedStudents(TeacherClass $teacherClass): int
    {
        $class = $teacherClass->classModel;
        $teacher = $teacherClass->teacher;

        if (!$class || !$teacher) return 0;

        $filters = $this->tableFilters;

        $academicYearId = $filters['academic_year_id']['value'] ?? academic_years::getActiveId();
        $month = $filters['month']['value'] ?? null;
        $week = $filters['week']['value'] ?? null;

        // حساب الطلاب المُقيمين بناءً على قيد الطلبة
        $evaluationQuery = StudentEvaluationRecord::query()
            ->where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYearId)
            ->whereHas('student.enrollments', function ($q) use ($class, $academicYearId) {
                $q->where('grade_id', $class->grade_id)
                    ->where('section_id', $class->section_id)
                    ->where('academic_year_id', $academicYearId);
            });

        if ($week) {
            $evaluationQuery->where('week', $week);
        }
        if ($month) {
            $evaluationQuery->where('month', $month);
        }

        return $evaluationQuery->distinct('student_id')->count('student_id');
    }

    /**
     * حساب حالة أسبوع معين لمعلم وصف محدد
     */
    private function getWeekStatus(TeacherClass $record, int $week): string
    {
        $month = $this->selectedMonth;
        $academicYearId = $this->tableFilters['academic_year_id']['value'] ?? academic_years::getActiveId();
        
        // إذا لم يتم اختيار شهر، اعرض رسالة
        if (!$month) {
            return '-';
        }
        
        $class = $record->classModel;
        $teacher = $record->teacher;
        
        if (!$class || !$teacher) {
            return '-';
        }
        
        // حساب إجمالي الطلاب المسجلين
        $totalStudents = StudentEnrollment::where('grade_id', $class->grade_id)
            ->where('section_id', $class->section_id)
            ->where('academic_year_id', $academicYearId)
            ->distinct('student_id')
            ->count('student_id');
        
        if ($totalStudents == 0) {
            return '-';
        }
        
        // حساب الطلاب المقيمين في هذا الأسبوع والشهر والمادة المحددة
        // نبحث عن التقييمات التي تمت بواسطة هذا المعلم في هذا الصف والمادة
        $query = StudentEvaluationRecord::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYearId)
            ->where('month', $month)
            ->where('week', $week)
            ->where('subject_id', $record->subject_id)  // تصفية مباشرة حسب المادة
            ->whereHas('student.enrollments', function ($q) use ($class, $academicYearId) {
                $q->where('grade_id', $class->grade_id)
                    ->where('section_id', $class->section_id)
                    ->where('academic_year_id', $academicYearId);
            });
            
        // التقييمات مصفاة حسب المعلم والمادة والصف المحددة - تم تبسيط الاستعلام لاستخدام subject_id مباشرة
        
        $evaluatedStudents = $query->distinct('student_id')
            ->count('student_id');
        
        return "{$evaluatedStudents}/{$totalStudents}";
    }
    
    /**
     * تحديد لون أسبوع معين بناء على حالة التقييم
     */
    private function getWeekStatusColor(TeacherClass $record, int $week): string
    {
        $month = $this->selectedMonth;
        $academicYearId = $this->tableFilters['academic_year_id']['value'] ?? academic_years::getActiveId();
        
        // إذا لم يتم اختيار شهر
        if (!$month) {
            return 'gray';
        }
        
        $class = $record->classModel;
        $teacher = $record->teacher;
        
        if (!$class || !$teacher) {
            return 'gray';
        }
        
        // حساب إجمالي الطلاب المسجلين
        $totalStudents = StudentEnrollment::where('grade_id', $class->grade_id)
            ->where('section_id', $class->section_id)
            ->where('academic_year_id', $academicYearId)
            ->distinct('student_id')
            ->count('student_id');
        
        if ($totalStudents == 0) {
            return 'gray';  // لا يوجد طلاب
        }
        
        // حساب الطلاب المقيمين في هذا الأسبوع والشهر للمادة المحددة
        // نبحث عن التقييمات التي تمت بواسطة هذا المعلم في هذا الصف والمادة
        $query = StudentEvaluationRecord::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $academicYearId)
            ->where('month', $month)
            ->where('week', $week)
            ->where('subject_id', $record->subject_id)  // تصفية مباشرة حسب المادة
            ->whereHas('student.enrollments', function ($q) use ($class, $academicYearId) {
                $q->where('grade_id', $class->grade_id)
                    ->where('section_id', $class->section_id)
                    ->where('academic_year_id', $academicYearId);
            });
            
        // التقييمات مصفاة حسب المعلم والمادة والصف المحددة - تم تبسيط الاستعلام
        
        $evaluatedStudents = $query->distinct('student_id')
            ->count('student_id');
        
        // تحديد اللون بناء على النسبة المئوية
        $percentage = $totalStudents > 0 ? ($evaluatedStudents / $totalStudents) * 100 : 0;
        
        if ($percentage == 100) {
            return 'success';  // أخضر - مكتمل 100%
        } elseif ($percentage >= 50) {
            return 'warning';  // أصفر - مكتمل جزئياً 50% أو أكثر
        } elseif ($percentage > 0) {
            return 'info';     // أزرق - بداية التقييم أقل من 50%
        } else {
            return 'danger';   // أحمر - لم يبدأ التقييم
        }
    }
}
