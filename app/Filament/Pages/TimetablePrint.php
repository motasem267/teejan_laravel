<?php

namespace App\Filament\Pages;

use App\Models\academic_years;
use App\Models\ClassModel;
use App\Models\Day;
use App\Models\Employee;
use App\Models\LessonTime;
use App\Models\SchoolSchedule;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use TCPDF;
use UnitEnum;

class TimetablePrint extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-printer';

    protected static ?string $navigationLabel = 'طباعة الجدول الدراسي';

    protected static string|UnitEnum|null $navigationGroup = 'ادارة الجدول';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'طباعة الجدول الدراسي';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'academic_year_id' => academic_years::getActiveId(),
        ]);
    }

    public function getView(): string
    {
        return 'filament.pages.timetable-print';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('خيارات التصدير')
                    ->schema([
                        Select::make('academic_year_id')
                            ->label('السنة الدراسية')
                            ->options(academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                            ->required()
                            ->live(),

                        Select::make('class_id')
                            ->label('الفصل (الصف + الشعبة)')
                            ->options(function (): array {
                                return ClassModel::with(['grade', 'section'])
                                    ->get()
                                    ->mapWithKeys(function ($c) {
                                        $label = ($c->grade?->name ?? '؟') . ' - ' . ($c->section?->name ?? '؟');
                                        return [$c->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->placeholder('اختر فصلاً  اتركه فارغاً لتصدير الكل'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function exportPdf(): mixed
    {
        $formData = $this->form->getState();

        $academicYearId = $formData['academic_year_id'] ?? null;

        if (!$academicYearId) {
            Notification::make()->danger()->title('اختر السنة الدراسية أولاً')->send();
            return null;
        }

        $classIdParam = $formData['class_id'] ?? null;

        // Load reference data
        $academicYear = academic_years::find($academicYearId);
        $yearLabel    = $academicYear?->year_label ?? '';
        $days         = Day::orderBy('day_order')->get();
        // we will calculate periods inside the class loop, because each class may only use a subset of lesson times

        // Determine which classes to build
        if ($classIdParam) {
            $classModels = ClassModel::with(['grade', 'section'])
                ->where('id', (int) $classIdParam)
                ->get();
        } else {
            $classModels = ClassModel::with(['grade', 'section'])->get();
        }

        if ($classModels->isEmpty()) {
            Notification::make()->warning()->title('لا توجد فصول للتصدير')->send();
            return null;
        }

        try {
            //  Build PDF via TCPDF 
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('نظام إدارة شؤون الطلاب');
            $pdf->SetAuthor('نظام إدارة شؤون الطلاب');
            $pdf->SetTitle('الجدول الدراسي - ' . $yearLabel);
            $pdf->SetSubject('الجدول الدراسي');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(12, 15, 12);
            $pdf->SetAutoPageBreak(true, 15);

            // Landscape A4 usable width  273mm (297 - 12 - 12)
            $pageWidth   = 273;
            $dayColW     = 25;
            // $periodColW will be calculated per-class after we know which periods are relevant

            $headerH = 12;
            $rowH    = 16;

            $headerBg = [30, 58, 95];
            $headerFg = [255, 255, 255];
            $dayBg    = [44, 82, 130];
            $dayFg    = [255, 255, 255];
            $altRowBg = [235, 242, 255];
            $emptyFg  = [170, 170, 170];

            foreach ($classModels as $classModel) {
                $pdf->AddPage();
                $pdf->SetFont('dejavusans', '', 11);
                $pdf->setRTL(true);

                $gradeName   = $classModel->grade?->name ?? '؟';
                $sectionName = $classModel->section?->name ?? '؟';

                //  Title 
                $pdf->SetFont('dejavusans', 'B', 14);
                $pdf->SetTextColor(30, 58, 95);
                $pdf->Cell($pageWidth, 8, 'الجدول الدراسي  ' . $gradeName . ' - ' . $sectionName, 0, 1, 'C');
                $pdf->SetFont('dejavusans', '', 10);
                $pdf->SetTextColor(80, 80, 80);
                if ($yearLabel) {
                    $pdf->Cell($pageWidth, 5, 'السنة الدراسية: ' . $yearLabel, 0, 1, 'C');
                }
                $pdf->Ln(4);

                //  Build schedule grid[day_id][period_id] 
                $schedules = SchoolSchedule::with(['teacherClass.subject', 'teacherClass.teacher'])
                    ->whereHas('teacherClass', function ($q) use ($classModel, $academicYearId) {
                        $q->where('class_id', $classModel->id)
                          ->where('academic_year_id', $academicYearId);
                    })
                    ->get();

                $grid = [];
                foreach ($schedules as $schedule) {
                    $tc = $schedule->teacherClass;
                    if (!$tc) {
                        continue;
                    }
                    $grid[$schedule->day_id][$schedule->lesson_time_id] = [
                        'subject' => $tc->subject?->name ?? '',
                        'teacher' => $tc->teacher?->name ?? '',
                    ];
                }

                // determine which lesson times are actually used by this class
                $periods = LessonTime::whereIn('id', $schedules->pluck('lesson_time_id')->unique())
                    ->orderBy('period_number')
                    ->get();

                // recalc column width based on the filtered periods list
                $periodCount = $periods->count();
                $periodColW  = $periodCount > 0
                    ? max(28, (int) round(($pageWidth - $dayColW) / $periodCount))
                    : 40;

                //  Header row 
                $pdf->SetFont('dejavusans', 'B', 9);
                $pdf->SetFillColor($headerBg[0], $headerBg[1], $headerBg[2]);
                $pdf->SetTextColor($headerFg[0], $headerFg[1], $headerFg[2]);
                $pdf->MultiCell($dayColW, $headerH, 'اليوم', 1, 'C', true, 0, '', '', true, 0, false, true, $headerH, 'M');

                foreach ($periods as $period) {
                    $startTime = \Carbon\Carbon::parse($period->start_time)->format('H:i');
                    $endTime   = \Carbon\Carbon::parse($period->end_time)->format('H:i');
                    $label     = 'الحصة ' . $period->period_number . "\n" . $startTime . ' - ' . $endTime;
                    $pdf->MultiCell($periodColW, $headerH, $label, 1, 'C', true, 0, '', '', true, 0, false, true, $headerH, 'M');
                }
                $pdf->Ln();

                //  Data rows 
                $pdf->SetFont('dejavusans', '', 9);
                $rowIndex = 0;
                foreach ($days as $day) {
                    $isAlt = ($rowIndex % 2 === 1);
                    $rowIndex++;

                    $pdf->SetFillColor($dayBg[0], $dayBg[1], $dayBg[2]);
                    $pdf->SetTextColor($dayFg[0], $dayFg[1], $dayFg[2]);
                    $pdf->MultiCell($dayColW, $rowH, $day->day_name_ar, 1, 'C', true, 0, '', '', true, 0, false, true, $rowH, 'M');

                    foreach ($periods as $period) {
                        $entry = $grid[$day->id][$period->id] ?? null;
                        $bgRow = $isAlt ? $altRowBg : [255, 255, 255];

                        $pdf->SetFillColor($bgRow[0], $bgRow[1], $bgRow[2]);

                        if ($entry) {
                            $pdf->SetTextColor(26, 54, 93);
                            $cellText = $entry['subject'] . "\n" . $entry['teacher'];
                        } else {
                            $pdf->SetTextColor($emptyFg[0], $emptyFg[1], $emptyFg[2]);
                            $cellText = '';
                        }

                        $pdf->MultiCell($periodColW, $rowH, $cellText, 1, 'C', true, 0, '', '', true, 0, false, true, $rowH, 'M');
                    }
                    $pdf->Ln();
                }
            }

            //  Stream 
            $safeYear = str_replace([' ', '/', '\\'], '_', $yearLabel);
            $filename = $classIdParam
                ? 'جدول_فصل_' . $classIdParam . '_' . $safeYear . '_' . date('Y_m_d') . '.pdf'
                : 'جدول_كل_الفصول_' . $safeYear . '_' . date('Y_m_d') . '.pdf';

            $content = $pdf->Output($filename, 'S');

            return response()->streamDownload(
                fn () => print($content),
                $filename,
                ['Content-Type' => 'application/pdf'],
            );

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('خطأ في التصدير')
                ->body('حدث خطأ: ' . $e->getMessage())
                ->send();

            return null;
        }
    }

    public static function canAccess(): bool
    {
        /** @var Employee|null $user */
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            return false;
        }
        return $user->hasPermission('timetable-print.view') || $user->hasPermission('school-schedules.view');
    }
}

