<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use App\Models\ActivityLog;
use App\Models\grade;
use App\Models\academic_years;
use App\Models\StudentEnrollment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;
class GradePromotion extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-circle';
    
    protected static ?string $navigationLabel = 'ترحيل الطلبة بالصف';
    
    protected static ?string $title = 'ترحيل الطلبة بالصف';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الطلاب وأولياء الامور';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];
    
    public array $selectedStudents = [];

    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function getView(): string
    {
        return 'filament.pages.grade-promotion';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('اختيار الصف والسنة الدراسية')
                    ->description('اختر الصف الحالي والسنة الدراسية لعرض الطلبة المسجلين')
                    ->schema([
                        Select::make('current_grade_id')
                            ->label('الصف الدراسي الحالي')
                            ->options(grade::all()->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->updateStudentsList()),

                        Select::make('current_academic_year_id')
                            ->label('السنة الدراسية الحالية')
                            ->options(academic_years::all()->pluck('year_label', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->updateStudentsList()),

                        Select::make('new_academic_year_id')
                            ->label('السنة الدراسية الجديدة (للترحيل)')
                            ->options(academic_years::all()->pluck('year_label', 'id'))
                            ->required()
                            ->helperText('السنة التي سيتم نقل الطلبة إليها'),
                    ])
                    ->columns(3),

                Section::make('الطلبة المسجلين في الصف')
                    ->description('اختر الطلبة المراد ترحيلهم للصف التالي')
                    ->schema([
                        CheckboxList::make('selected_students')
                            ->label('الطلبة')
                            ->options(function (callable $get): array {
                                $gradeId = $get('current_grade_id');
                                $academicYearId = $get('current_academic_year_id');

                                if (!$gradeId || !$academicYearId) {
                                    return [];
                                }

                                return StudentEnrollment::where('grade_id', $gradeId)
                                    ->where('academic_year_id', $academicYearId)
                                    ->with('student')
                                    ->get()
                                    ->filter(fn ($e) => $e->student && $e->student->status_id === 1) // نشط فقط
                                    ->mapWithKeys(function ($enrollment) {
                                        return [
                                            $enrollment->student_id => $enrollment->student->full_name . 
                                                ' (الرقم الوطني: ' . $enrollment->student->national_id . ')'
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->columns(2)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->helperText('يمكنك تحديد جميع الطلبة أو اختيار طلبة معينين فقط'),
                    ])
                    ->visible(fn (callable $get): bool => 
                        filled($get('current_grade_id')) && filled($get('current_academic_year_id'))
                    ),
            ])
            ->statePath('data');
    }

    protected function updateStudentsList(): void
    {
        // يتم تحديث قائمة الطلبة تلقائياً عند تغيير الصف أو السنة
    }

    public function promote(): void
    {
        $data = $this->form->getState();

        // التحقق من البيانات
        if (empty($data['current_grade_id']) || 
            empty($data['current_academic_year_id']) || 
            empty($data['new_academic_year_id'])) {
            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('يجب اختيار الصف والسنة الدراسية الحالية والجديدة.')
                ->send();
            return;
        }

        if (empty($data['selected_students'])) {
            Notification::make()
                ->warning()
                ->title('تنبيه')
                ->body('يجب اختيار طالب واحد على الأقل للترحيل.')
                ->send();
            return;
        }

        // البحث عن الصف التالي (حسب ID)
        $currentGrade = grade::find($data['current_grade_id']);
        $nextGrade = grade::where('id', '>', $currentGrade->id)
            ->orderBy('id', 'asc')
            ->first();

        if (!$nextGrade) {
            Notification::make()
                ->warning()
                ->title('تنبيه')
                ->body('لا يوجد صف دراسي تالي لترحيل الطلبة إليه.')
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            $promotedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($data['selected_students'] as $studentId) {
                // التحقق من عدم وجود تسجيل مسبق
                $exists = StudentEnrollment::where('student_id', $studentId)
                    ->where('grade_id', $nextGrade->id)
                    ->where('academic_year_id', $data['new_academic_year_id'])
                    ->exists();

                if ($exists) {
                    $student = \App\Models\student::find($studentId);
                    $skippedCount++;
                    $errors[] = "الطالب {$student->full_name} مسجل بالفعل في الصف التالي.";
                    continue;
                }

                // إضافة تسجيل جديد في الصف التالي
                StudentEnrollment::create([
                    'student_id' => $studentId,
                    'grade_id' => $nextGrade->id,
                    'academic_year_id' => $data['new_academic_year_id'],
                ]);

                $promotedCount++;
            }

            DB::commit();
            
            // تسجيل الحدث
            try {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'promoted',
                    'description' => "ترحيل {$promotedCount} طالب/طالبة إلى الصف: {$nextGrade->name}",
                    'model_type' => StudentEnrollment::class,
                    'model_id' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Ignore logging errors
            }

            // عرض رسالة النجاح
            $message = "تم ترحيل {$promotedCount} طالب/طالبة بنجاح إلى {$nextGrade->name}.";
            if ($skippedCount > 0) {
                $message .= " تم تخطي {$skippedCount} طالب/طالبة (مسجلين مسبقاً).";
            }

            Notification::make()
                ->success()
                ->title('نجح الترحيل')
                ->body($message)
                ->send();

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    Notification::make()
                        ->warning()
                        ->title('تنبيه')
                        ->body($error)
                        ->send();
                }
            }

            // إعادة تعيين النموذج
            $this->form->fill([
                'current_grade_id' => null,
                'current_academic_year_id' => null,
                'new_academic_year_id' => null,
                'selected_students' => [],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('حدث خطأ أثناء ترحيل الطلبة: ' . $e->getMessage())
                ->send();
        }
    }

    public static function canAccess(): bool
    {
        // التحقق من صلاحية الوصول
        /** @var Employee|null $employee */
        $employee = Auth::user();
        if (!$employee) {
            return false;
        }

        if (!method_exists($employee, 'hasPermission')) {
            return false;
        }

        // التحقق من صلاحية ترحيل الطلاب أو صفحة ترحيل الطلبة
        return $employee->hasPermission('grade-promotion.view') || 
               $employee->hasPermission('student_enrollments.promote');
    }
}
