<?php

namespace App\Filament\Resources\StudentEnrollments\Pages;

use App\Filament\Resources\StudentEnrollments\StudentEnrollmentResource;
use App\Models\academic_years;
use App\Models\grade;
use App\Models\Section;
use App\Models\student;
use App\Models\StudentEnrollment;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\DB;

class CreateStudentEnrollment extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = StudentEnrollmentResource::class;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => academic_years::getActiveId(),
        ]);
    }

    public function getView(): string
    {
        return 'filament.pages.create-student-enrollment';
    }

    public function getTitle(): string
    {
        return 'إنشاء قيد طلبة';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make('بيانات القيد')
                    ->description('اختر الصف والشعبة والسنة الدراسية التي سيتم قيد الطلبة فيها')
                    ->schema([
                        Select::make('grade_id')
                            ->label('الصف الدراسي')
                            ->options(grade::orderBy('id')->pluck('name', 'id'))
                            ->required()
                            ->live(),

                        Select::make('section_id')
                            ->label('الشعبة')
                            ->options(Section::orderBy('name')->pluck('name', 'id'))
                            ->required(),

                        Select::make('academic_year_id')
                            ->label('السنة الدراسية')
                            ->options(academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                            ->required()
                            ->live(),
                    ])
                    ->columns(3),

                SchemaSection::make('اختيار الطلبة')
                    ->description('يُعرض فقط الطلبة النشطون غير المسجلين في السنة الدراسية المختارة')
                    ->schema([
                        CheckboxList::make('selected_students')
                            ->label('الطلبة')
                            ->options(function (Get $get): array {
                                $academicYearId = $get('academic_year_id');

                                // جلب IDs الطلبة المسجلين مسبقاً في هذه السنة
                                $enrolledIds = $academicYearId
                                    ? StudentEnrollment::where('academic_year_id', $academicYearId)
                                        ->pluck('student_id')
                                        ->toArray()
                                    : [];

                                return student::where('status_id', 1) // نشط فقط
                                    ->whereNotIn('id', $enrolledIds)
                                    ->orderBy('full_name')
                                    ->get()
                                    ->mapWithKeys(fn ($s) => [
                                        $s->id => $s->full_name . ' (' . $s->national_id . ')',
                                    ])
                                    ->toArray();
                            })
                            ->columns(2)
                            ->gridDirection('row')
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('يمكنك تحديد جميع الطلبة أو اختيار طلبة معينين فقط'),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        if (empty($data['grade_id']) || empty($data['section_id']) || empty($data['academic_year_id'])) {
            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('يجب اختيار الصف والشعبة والسنة الدراسية.')
                ->send();
            return;
        }

        if (empty($data['selected_students'])) {
            Notification::make()
                ->warning()
                ->title('تنبيه')
                ->body('يجب اختيار طالب واحد على الأقل.')
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            $createdCount = 0;
            $skippedCount = 0;

            foreach ($data['selected_students'] as $studentId) {
                $exists = StudentEnrollment::where('student_id', $studentId)
                    ->where('grade_id', $data['grade_id'])
                    ->where('academic_year_id', $data['academic_year_id'])
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                StudentEnrollment::create([
                    'student_id'       => $studentId,
                    'grade_id'         => $data['grade_id'],
                    'section_id'       => $data['section_id'],
                    'academic_year_id' => $data['academic_year_id'],
                ]);

                $createdCount++;
            }

            DB::commit();

            $message = "تم إنشاء قيد {$createdCount} طالب/طالبة بنجاح.";
            if ($skippedCount > 0) {
                $message .= " تم تخطي {$skippedCount} طالب/طالبة (مسجلين مسبقاً في نفس الصف والسنة).";
            }

            Notification::make()
                ->success()
                ->title('تم إنشاء القيود')
                ->body($message)
                ->send();

            // إعادة تعيين قائمة الطلبة مع الإبقاء على بيانات الصف والشعبة والسنة
            $this->form->fill([
                'grade_id'          => $data['grade_id'],
                'section_id'        => $data['section_id'],
                'academic_year_id'  => $data['academic_year_id'],
                'selected_students' => [],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->danger()
                ->title('خطأ')
                ->body('حدث خطأ أثناء إنشاء القيود: ' . $e->getMessage())
                ->send();
        }
    }

    public function getBreadcrumbs(): array
    {
        return [
            StudentEnrollmentResource::getUrl('index') => 'قيد الطلبة',
            '#' => 'إنشاء قيد طلبة',
        ];
    }
}
