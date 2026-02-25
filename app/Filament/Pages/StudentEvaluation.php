<?php

namespace App\Filament\Pages;

use App\Models\academic_years;
use App\Models\ActivityLog;
use App\Models\ClassModel;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationStudentAnswer;
use App\Models\EvaluationType;
use App\Models\student;
use App\Models\StudentEnrollment;
use App\Models\subject;
use App\Models\TeacherClass;
use App\Models\StudentEvaluationRecord;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class StudentEvaluation extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'تقييم الطلاب';
    protected static string|UnitEnum|null $navigationGroup = 'التقييم والدرجات';
    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }
        
        return $user->hasPermission('student-evaluation.view') || 
               $user->hasPermission('student-evaluation.create');
    }

    public ?array $data = [];
    public ?int $selectedClassId = null;
    public ?int $selectedSubjectId = null;
    public ?int $selectedAcademicYearId = null;
    public ?int $selectedWeek = null;
    public ?int $selectedMonth = null;

    public array $students = [];
    public array $questions = [];
    public array $answers = [];
    public array $studentAnswers = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $teacherId = Auth::id();

        return $schema
            ->schema([
                Section::make('معلومات التقييم')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('class_id')
                                    ->label('الصف الدراسي')
                                    ->options(function () use ($teacherId) {
                                        if (!$teacherId) {
                                            return [];
                                        }
                                        
                                        // Check if teacher_classes table exists
                                        if (!\Illuminate\Support\Facades\Schema::hasTable('teacher_classes')) {
                                            return [];
                                        }
                                        
                                        try {
                                            return TeacherClass::where('teacher_id', $teacherId)
                                                ->with(['classModel.grade', 'classModel.section'])
                                                ->get()
                                                ->mapWithKeys(function ($teacherClass) {
                                                    if (!$teacherClass->classModel) {
                                                        return [];
                                                    }
                                                    $class = $teacherClass->classModel;
                                                    $gradeName = $class->grade->name ?? 'غير محدد';
                                                    $sectionName = $class->section->name ?? 'غير محدد';
                                                    $label = $gradeName . ' - ' . $sectionName;
                                                    return [$class->id => $label];
                                                })
                                                ->filter()
                                                ->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state) {
                                        $this->selectedClassId = $state;
                                        $this->loadStudents();
                                        $this->data['subject_id'] = null;
                                    }),

                                Select::make('subject_id')
                                    ->label('المادة')
                                    ->options(function () use ($teacherId) {
                                        if (!$teacherId || !$this->selectedClassId) {
                                            return [];
                                        }
                                        
                                        return TeacherClass::where('teacher_id', $teacherId)
                                            ->where('class_id', $this->selectedClassId)
                                            ->with('subject')
                                            ->get()
                                            ->mapWithKeys(function ($teacherClass) {
                                                if (!$teacherClass->subject) {
                                                    return [];
                                                }
                                                return [$teacherClass->subject->id => $teacherClass->subject->name];
                                            })
                                            ->filter()
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state) {
                                        $this->selectedSubjectId = $state;
                                        $this->loadQuestions();
                                        $this->loadStudents(); // Reload students when subject changes
                                    }),

                                Select::make('academic_year_id')
                                    ->label('السنة الدراسية')
                                    ->options(academic_years::pluck('year_label', 'id'))
                                    ->default(academic_years::getActiveId())
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state) {
                                        $this->selectedAcademicYearId = $state;
                                        $this->loadStudents(); // Reload students when academic year changes
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('month')
                                    ->label('الشهر')
                                    ->options([
                                        1 => 'يناير',
                                        2 => 'فبراير',
                                        3 => 'مارس',
                                        4 => 'أبريل',
                                        5 => 'مايو',
                                        6 => 'يونيو',
                                        7 => 'يوليو',
                                        8 => 'أغسطس',
                                        9 => 'سبتمبر',
                                        10 => 'أكتوبر',
                                        11 => 'نوفمبر',
                                        12 => 'ديسمبر',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state) {
                                        $this->selectedMonth = $state;
                                        $this->loadStudents(); // Reload students when month changes
                                    }),

                                Select::make('week')
                                    ->label('الأسبوع')
                                    ->options([
                                        1 => 'الأسبوع الأول',
                                        2 => 'الأسبوع الثاني',
                                        3 => 'الأسبوع الثالث',
                                        4 => 'الأسبوع الرابع',
                                        5 => 'الأسبوع الخامس',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state) {
                                        $this->selectedWeek = $state;
                                        $this->loadStudents(); // Reload students when week changes
                                    }),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function loadStudents(): void
    {
        if (!$this->selectedClassId) {
            $this->students = [];
            return;
        }

        $class = ClassModel::find($this->selectedClassId);
        $activeAcademicYear = academic_years::getActiveId();

        if ($class) {
            // Query students through student_enrollments table
            $allStudents = student::whereHas('enrollments', function ($query) use ($class, $activeAcademicYear) {
                $query->where('grade_id', $class->grade_id)
                      ->where('section_id', $class->section_id)
                      ->where('academic_year_id', $activeAcademicYear);
            })
            ->orderBy('full_name')
            ->get();

            // Filter out students who have been fully evaluated
            $filteredStudents = [];
            foreach ($allStudents as $student) {
                if (!$this->isStudentFullyEvaluated($student->id)) {
                    $filteredStudents[] = $student->toArray();
                }
            }
            
            $this->students = $filteredStudents;
        }
    }

    /**
     * Check if student has been fully evaluated for selected criteria
     */
    protected function isStudentFullyEvaluated(int $studentId): bool
    {
        // Only check if we have all required data
        if (!$this->selectedSubjectId || !$this->selectedMonth || !$this->selectedWeek) {
            return false;
        }

        $teacherId = Auth::id();
        $academicYearId = $this->selectedAcademicYearId ?? academic_years::getActiveId();
        
        // Get the number of questions for this evaluation type
        $evaluationType = EvaluationType::where('code', '2020')->first();
        if (!$evaluationType) {
            return false;
        }
        
        $totalQuestions = EvaluationQuestion::where('evaluation_type_id', $evaluationType->id)
            ->where('is_active', true)
            ->count();
        
        if ($totalQuestions == 0) {
            return false;
        }
        
        // Count how many questions this student has answered
        $answeredQuestions = StudentEvaluationRecord::where('student_id', $studentId)
            ->where('teacher_id', $teacherId)
            ->where('month', $this->selectedMonth)
            ->where('week', $this->selectedWeek)
            ->where('academic_year_id', $academicYearId)
            ->distinct('question_id')
            ->count();
        
        // Student is fully evaluated if answered all questions
        return $answeredQuestions >= $totalQuestions;
    }

    protected function loadQuestions(): void
    {
        if (!$this->selectedSubjectId) {
            $this->questions = [];
            $this->answers = [];
            return;
        }

        // Get evaluation type for teachers (code: 2020)
        $evaluationType = EvaluationType::where('code', '2020')->first();
        
        if (!$evaluationType) {
            $this->questions = [];
            $this->answers = [];
            return;
        }

        // Load questions for this evaluation type
        $questions = EvaluationQuestion::where('evaluation_type_id', $evaluationType->id)
            ->where('is_active', true)
            ->with(['sharedAnswers' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        $this->questions = $questions->toArray();
        
        // Prepare answers grouped by question
        $this->answers = [];
        foreach ($questions as $question) {
            $this->answers[$question->id] = $question->sharedAnswers->pluck('label', 'id')->toArray();
        }
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            if (empty($this->students) || empty($this->questions)) {
                Notification::make()
                    ->title('خطأ')
                    ->body('يرجى اختيار الصف والمادة أولاً')
                    ->danger()
                    ->send();
                return;
            }

            if (empty($this->studentAnswers)) {
                Notification::make()
                    ->title('خطأ')
                    ->body('يرجى إدخال إجابات للطلاب')
                    ->danger()
                    ->send();
                return;
            }

            // Validate complete answers for each student
            $incompleteStudents = [];
            foreach ($this->studentAnswers as $studentId => $studentQuestions) {
                $answeredQuestions = 0;
                $totalQuestions = 0;
                
                foreach ($this->questions as $question) {
                    $totalQuestions++;
                    if (!empty($studentQuestions[$question['id']])) {
                        $answeredQuestions++;
                    }
                }
                
                if ($answeredQuestions > 0 && $answeredQuestions < $totalQuestions) {
                    $studentName = collect($this->students)->firstWhere('id', $studentId)['full_name'] ?? 'طالب غير معروف';
                    $incompleteStudents[] = $studentName;
                }
            }
            
            if (!empty($incompleteStudents)) {
                Notification::make()
                    ->title('تقييم غير مكتمل')
                    ->body('يجب الإجابة على جميع الأسئلة للطلاب التالين: ' . implode('، ', $incompleteStudents))
                    ->warning()
                    ->send();
                return;
            }

            DB::beginTransaction();

            $teacherId = Auth::id();
            $evaluationType = EvaluationType::where('code', '2020')->first();
            $class = ClassModel::find($this->selectedClassId);

            foreach ($this->students as $student) {
                $studentId = $student['id'];
                
                // Check if student has any answers
                $hasAnswers = false;
                foreach ($this->questions as $question) {
                    if (!empty($this->studentAnswers[$studentId][$question['id']])) {
                        $hasAnswers = true;
                        break;
                    }
                }

                if (!$hasAnswers) {
                    continue;
                }

                // Create evaluation record
                $evaluation = Evaluation::create([
                    'evaluation_type_id' => $evaluationType->id,
                    'evaluator_id' => $teacherId,
                    'student_id' => $studentId,
                    'subject_id' => $data['subject_id'],
                    'grade_id' => $class->grade_id,
                    'section_id' => $class->section_id,
                    'academic_year_id' => $data['academic_year_id'],
                    'week_no' => $data['week'],
                ]);

                // Save student answers
                foreach ($this->questions as $question) {
                    $answerId = $this->studentAnswers[$studentId][$question['id']] ?? null;

                    if ($answerId) {
                        EvaluationStudentAnswer::create([
                            'evaluation_id' => $evaluation->id,
                            'question_id' => $question['id'],
                            'answer_id' => $answerId,
                        ]);

                        // Save to new student_evaluations table
                        StudentEvaluationRecord::create([
                            'student_id' => $studentId,
                            'question_id' => $question['id'],
                            'answer_id' => $answerId,
                            'month' => $data['month'],
                            'week' => $data['week'],
                            'academic_year_id' => $data['academic_year_id'],
                            'teacher_id' => $teacherId,
                        ]);
                    }
                }
            }

            DB::commit();
            
            // تسجيل الحدث
            try {
                $evaluatedStudentsCount = count(array_filter($this->studentAnswers, function($answers) {
                    return !empty($answers);
                }));
                
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'created',
                    'description' => "حفظ تقييمات {$evaluatedStudentsCount} طالب/طالبة (الأسبوع: " . ($data['week'] ?? '-') . ", الشهر: " . ($data['month'] ?? '-') . ")",
                    'model_type' => StudentEvaluationRecord::class,
                    'model_id' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Ignore logging errors
            }

            Notification::make()
                ->title('تم الحفظ بنجاح')
                ->body('تم حفظ تقييمات الطلاب بنجاح')
                ->success()
                ->send();

            // Reset form
            $this->studentAnswers = [];
            $this->form->fill();
            
            // Reload students to hide fully evaluated ones
            $this->loadStudents();

        } catch (Halt $exception) {
            DB::rollBack();
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getView(): string
    {
        return 'filament.pages.student-evaluation';
    }
    
    public function getTitle(): string
    {
        return 'تقييم الطلاب';
    }
    
    public static function getNavigationLabel(): string
    {
        return 'تقييم الطلاب';
    }
}
