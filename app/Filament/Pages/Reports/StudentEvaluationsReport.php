<?php

namespace App\Filament\Pages\Reports;

use App\Models\academic_years;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\student;
use App\Models\StudentEvaluationRecord;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use TCPDF;

class StudentEvaluationsReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'تقرير تقييمات الطلاب';
    protected static string|\UnitEnum|null $navigationGroup = 'تقارير';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.reports.student-evaluations-report';
    protected static ?string $title = 'تقرير تقييمات الطلاب';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        return $user->hasPermission('student-evaluations-report.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(StudentEvaluationRecord::query())
            ->columns([
                TextColumn::make('student.full_name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('question.label')
                    ->label('السؤال')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('answer.label')
                    ->label('الإجابة')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('teacher.name')
                    ->label('المعلم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('academicYear.year_label')
                    ->label('السنة الدراسية')
                    ->sortable(),

                TextColumn::make('month')
                    ->label('الشهر')
                    ->formatStateUsing(function ($state) {
                        $months = [
                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                        ];
                        return $months[$state] ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('week')
                    ->label('الأسبوع')
                    ->formatStateUsing(function ($state) {
                        $weeks = [
                            1 => 'الأول', 2 => 'الثاني', 3 => 'الثالث',
                            4 => 'الرابع', 5 => 'الخامس'
                        ];
                        return $weeks[$state] ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ التقييم')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('student_id')
                    ->label('الطالب')
                    ->options(
                        student::whereNotNull('full_name')
                            ->orderBy('full_name')
                            ->pluck('full_name', 'id')
                            ->filter()
                    )
                    ->searchable(),

                SelectFilter::make('teacher_id')
                    ->label('المعلم')
                    ->options(
                        Employee::whereHas('teacherClasses')
                            ->whereNotNull('name')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->filter()
                    )
                    ->searchable(),

                SelectFilter::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->options(
                        academic_years::whereNotNull('year_label')
                            ->orderBy('year_label', 'desc')
                            ->pluck('year_label', 'id')
                            ->filter()
                    )
                    ->default(academic_years::getActiveId()),

                SelectFilter::make('month')
                    ->label('الشهر')
                    ->options([
                        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
                    ]),

                SelectFilter::make('week')
                    ->label('الأسبوع')
                    ->options([
                        1 => 'الأسبوع الأول',
                        2 => 'الأسبوع الثاني',
                        3 => 'الأسبوع الثالث',
                        4 => 'الأسبوع الرابع',
                        5 => 'الأسبوع الخامس',
                    ]),

                SelectFilter::make('question_id')
                    ->label('السؤال')
                    ->options(function () {
                        return StudentEvaluationRecord::whereHas('question', function ($query) {
                                $query->whereNotNull('label');
                            })
                            ->with('question')
                            ->get()
                            ->pluck('question.label', 'question_id')
                            ->filter()
                            ->unique()
                            ->take(20);
                    })
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public function exportToPdf()
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'hasPermission') &&
            !$user->hasPermission('student-evaluations-report.export-pdf')) {

            Notification::make()
                ->title('خطأ في الصلاحيات')
                ->body('ليس لديك صلاحية لتصدير التقييمات إلى PDF')
                ->danger()
                ->send();

            return;
        }

        try {

            $query = StudentEvaluationRecord::query()
                ->with(['student', 'question', 'answer', 'teacher', 'academicYear']);

            if (property_exists($this, 'tableFilters') && !empty($this->tableFilters)) {

                $filters = $this->tableFilters;

                if (!empty($filters['student_id']['value'])) {
                    $query->where('student_id', $filters['student_id']['value']);
                }

                if (!empty($filters['teacher_id']['value'])) {
                    $query->where('teacher_id', $filters['teacher_id']['value']);
                }

                if (!empty($filters['academic_year_id']['value'])) {
                    $query->where('academic_year_id', $filters['academic_year_id']['value']);
                }

                if (!empty($filters['month']['value'])) {
                    $query->where('month', $filters['month']['value']);
                }

                if (!empty($filters['week']['value'])) {
                    $query->where('week', $filters['week']['value']);
                }

                if (!empty($filters['question_id']['value'])) {
                    $query->where('question_id', $filters['question_id']['value']);
                }

            } else {

                $activeYear = academic_years::getActiveId();

                if ($activeYear) {
                    $query->where('academic_year_id', $activeYear);
                }
            }

            $evaluations = $query->orderBy('created_at', 'desc')->get();

        } catch (\Exception $e) {

            Log::error('خطأ في جلب بيانات التقييم: ' . $e->getMessage());

            Notification::make()
                ->title('خطأ في جلب البيانات')
                ->body('حدث خطأ أثناء جلب بيانات التقييم')
                ->danger()
                ->send();

            return;
        }

        if ($evaluations->isEmpty()) {

            Notification::make()
                ->title('تنبيه')
                ->body('لا توجد بيانات للتصدير')
                ->warning()
                ->send();

            return;
        }

        // تعريف المتغيرات المطلوبة
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        
        $weeks = [1 => 'الأول', 2 => 'الثاني', 3 => 'الثالث', 4 => 'الرابع', 5 => 'الخامس'];

        try {
            // إعدادات PDF محسنة
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('نظام إدارة شؤون الطلاب');
            $pdf->SetAuthor('نظام إدارة شؤون الطلاب');
            $pdf->SetTitle('تقرير تقييمات الطلاب');
            $pdf->SetSubject('تقرير شامل لتقييمات الطلاب');
            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 25, 15);
            $pdf->SetAutoPageBreak(true, 25);
            
            $pdf->AddPage();
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->setRTL(true);
            
            // إنشاء Header عام
            $this->addMainHeader($pdf);
            
            $pdf->Ln(5);
            
            // تجميع البيانات حسب الطالب والشهر والأسبوع
            $groupedData = $this->groupEvaluations($evaluations, $months, $weeks);
            $weeks = [1 => 'الأول', 2 => 'الثاني', 3 => 'الثالث', 4 => 'الرابع', 5 => 'الخامس'];

            // عرض البيانات المجمعة
            foreach ($groupedData as $groupKey => $group) {
                // فحص الحاجة لصفحة جديدة
                if ($pdf->GetY() > 240) {
                    $pdf->AddPage();
                }
                
                // عرض عنوان المجموعة
                $this->addGroupHeader($pdf, $group['student_name'], $group['month_name'], $group['week_name']);
                
                // عرض جدول التقييمات لهذة المجموعة
                $this->addEvaluationsTable($pdf, $group['evaluations']);
                
                $pdf->Ln(8); // مسافة بين المجموعات
            }
            
            // إنهاء الملف

            $filenameParts = ['تقرير_تقييمات'];

            // الحصول على الفلاتر الحالية لتخصيص اسم الملف
            $currentFilters = property_exists($this, 'tableFilters') ? $this->tableFilters : [];

            if (!empty($currentFilters['student_id']['value'])) {
                $studentName = student::find($currentFilters['student_id']['value'])->full_name ?? 'طالب';
                $filenameParts[] = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $studentName);
            }

            if (!empty($currentFilters['month']['value'])) {
                $monthValue = $currentFilters['month']['value'];
                $filenameParts[] = $months[$monthValue] ?? $monthValue;
            }

            if (!empty($currentFilters['week']['value'])) {
                $weekValue = $currentFilters['week']['value'];
                $filenameParts[] = 'الاسبوع_' . ($weeks[$weekValue] ?? $weekValue);
            }

            if (!empty($currentFilters['academic_year_id']['value'])) {
                $yearId = $currentFilters['academic_year_id']['value'];
                $yearLabel = academic_years::find($yearId)->year_label ?? null;
                if ($yearLabel) {
                    $filenameParts[] = str_replace([' ', '/', '\\', '-'], '_', $yearLabel);
                }
            }

            $filenameParts[] = date('Y_m_d_H_i_s');

            $filename = implode('_', $filenameParts) . '.pdf';

            // تسجيل الحدث
            try {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'exported',
                    'description' => 'تصدير تقرير تقييمات الطلاب إلى PDF (عدد: ' . $evaluations->count() . ')',
                    'model_type' => StudentEvaluationRecord::class,
                    'model_id' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Ignore logging errors
            }

            // ✅ التعديل المهم هنا
            $content = $pdf->Output($filename, 'S');

            return response()->streamDownload(
                fn () => print($content),
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                ]
            );

        } catch (\Exception $e) {

            Log::error('خطأ في تصدير PDF: ' . $e->getMessage());

            Notification::make()
                ->title('خطأ في التصدير')
                ->body('حدث خطأ أثناء تصدير التقرير')
                ->danger()
                ->send();
        }
    }

    /**
     * إضافة header عام بسيط
     */
    private function addMainHeader($pdf)
    {
        // خلفية Header
        $pdf->SetFillColor(41, 128, 185);
        $pdf->Rect(15, 15, 180, 20, 'F');
        
        // العنوان
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('dejavusans', 'B', 18);
        $pdf->SetXY(15, 20);
        $pdf->Cell(180, 8, 'تقرير تقييمات الطلاب', 0, 1, 'C');
        
        // تاريخ التوليد
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY(15, 28);
        $currentDate = date('Y/m/d H:i');
        $pdf->Cell(180, 5, "تاريخ التوليد: $currentDate", 0, 1, 'C');
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, 40);
    }
    
    /**
     * تجميع التقييمات حسب الطالب والشهر والأسبوع
     */
    private function groupEvaluations($evaluations, $months, $weeks)
    {
        $groups = [];
        
        foreach ($evaluations as $evaluation) {
            $studentName = $evaluation->student->full_name ?? 'غير محدد';
            $month = $evaluation->month;
            $week = $evaluation->week;
            
            $key = $studentName . '_' . $month . '_' . $week;
            
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'student_name' => $studentName,
                    'month_name' => $months[$month] ?? $month,
                    'week_name' => $weeks[$week] ?? $week,
                    'evaluations' => []
                ];
            }
            
            $groups[$key]['evaluations'][] = $evaluation;
        }
        
        return $groups;
    }
    
    /**
     * إضافة عنوان المجموعة
     */
    private function addGroupHeader($pdf, $studentName, $monthName, $weekName)
    {
        // حساب عرض النص وتوسيطه
        $headerWidth = 160; // نفس عرض الجدول
        $startX = 25; // نفس موضع بداية الجدول
        
        // خلفية العنوان
        $pdf->SetFillColor(52, 152, 219);
        $pdf->Rect($startX, $pdf->GetY(), $headerWidth, 12, 'F');
        
        // نص العنوان
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('dejavusans', 'B', 12);
        $currentY = $pdf->GetY();
        $pdf->SetXY($startX, $currentY + 2);
        $pdf->Cell($headerWidth, 8, "$studentName - $monthName - الأسبوع $weekName", 0, 1, 'C');
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(2);
    }
    
    /**
     * إضافة جدول تقييمات لمجموعة واحدة
     */
    private function addEvaluationsTable($pdf, $evaluations)
    {
        // header الجدول
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(180, 180, 180);
        
        // توسيط الجدول - العرض الإجمالي 160mm، المسافة المتبقية 20mm، فنبدأ من 25mm بدلاً من 15mm
        $startX = 25;
        $pdf->SetX($startX);
        
        $pdf->Cell(60, 8, 'السؤال', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'الإجابة', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'المعلم', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'تاريخ التقييم', 1, 1, 'C', true);
        
        // صفوف البيانات
        $pdf->SetFont('dejavusans', '', 9);
        $rowIndex = 0;
        
        foreach ($evaluations as $evaluation) {
            $isEvenRow = $rowIndex % 2 == 0;
            $rowColor = $isEvenRow ? [248, 249, 250] : [255, 255, 255];
            
            $pdf->SetFillColor($rowColor[0], $rowColor[1], $rowColor[2]);
            
            // إزالة القص وإبقاء النص كاملًا
            $questionText = $evaluation->question->label ?? '-';
            $answerText = $evaluation->answer->label ?? '-';
            $teacherName = $evaluation->teacher->name ?? '-';
            $dateText = $evaluation->created_at->format('Y-m-d');
            
            // حساب ارتفاع الصف بناءً على طول السؤال
            $pdf->SetFont('dejavusans', '', 9);
            $questionLines = $pdf->getNumLines($questionText, 60);
            $rowHeight = max(7, $questionLines * 4);
            
            // حفظ الموقع الحالي
            $currentY = $pdf->GetY();
            
            // رسم الخلايا مع الحدود - بنفس الترتيب الأصلي
            $pdf->SetX($startX);
            $pdf->MultiCell(60, $rowHeight, $questionText, 1, 'R', true, 0, '', '', true, 0, false, true, $rowHeight, 'T');
            $pdf->MultiCell(30, $rowHeight, $answerText, 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
            $pdf->MultiCell(40, $rowHeight, $teacherName, 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
            $pdf->MultiCell(30, $rowHeight, $dateText, 1, 'C', true, 1, '', '', true, 0, false, true, $rowHeight, 'M');
            
            $rowIndex++;
        }
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('تصدير PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportToPdf')
                ->visible(function () {
                    $user = Auth::user();
                    return $user && method_exists($user, 'hasPermission')
                        && $user->hasPermission('student-evaluations-report.export-pdf');
                }),
        ];
    }
}
