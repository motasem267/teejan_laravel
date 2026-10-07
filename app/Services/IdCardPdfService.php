<?php

namespace App\Services;

use App\Models\academic_years;
use App\Models\Employee;
use App\Models\student;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use TCPDF;

/**
 * توليد بطاقات التعريف (الطلبة والموظفين) بصيغة PDF.
 *
 * مقاس البطاقة القياسي CR80 (85.6 × 54 مم) وهو مقاس البطاقات البنكية.
 * تحتوي على مربع فارغ لتدبيس الصورة الشخصية.
 *
 * يدعم تخطيطين:
 *  - a4   : 10 بطاقات في صفحة A4 جاهزة للقص.
 *  - card : بطاقة واحدة لكل صفحة بمقاس البطاقة (لطابعات البطاقات البلاستيكية).
 */
class IdCardPdfService
{
    public const LAYOUT_A4 = 'a4';
    public const LAYOUT_CARD = 'card';

    public const TYPE_STUDENT = 'student';
    public const TYPE_EMPLOYEE = 'employee';

    private const CARD_W = 85.6;
    private const CARD_H = 54.0;

    // تخطيط A4: عمودان × خمسة صفوف
    private const A4_COLS = 2;
    private const A4_ROWS = 5;
    private const A4_GAP_X = 6.0;
    private const A4_GAP_Y = 3.0;

    private const FONT = 'aealarabiya';
    private const FONT_LATIN = 'dejavusans';

    private const COLOR_DARK = [33, 33, 36];
    private const COLOR_GOLD = [214, 160, 90];
    private const COLOR_GOLD_LIGHT = [250, 243, 231];
    private const COLOR_TEXT = [30, 30, 30];
    private const COLOR_MUTED = [120, 120, 120];
    private const COLOR_BORDER = [200, 200, 200];

    // لون الشريط العلوي حسب نوع البطاقة
    private const HEADER_COLORS = [
        self::TYPE_STUDENT => [33, 33, 36],
        self::TYPE_EMPLOYEE => [92, 62, 26],
    ];

    public static function layoutOptions(): array
    {
        return [
            self::LAYOUT_A4 => 'ورق A4 (10 بطاقات في الصفحة جاهزة للقص)',
            self::LAYOUT_CARD => 'طابعة بطاقات (بطاقة واحدة في كل صفحة بمقاس 85.6×54 مم)',
        ];
    }

    /**
     * @param  Collection<int, student>  $students
     */
    public function students(Collection $students, ?int $academicYearId, string $layout = self::LAYOUT_A4): string
    {
        $academicYearId ??= academic_years::getActiveId();
        $yearLabel = $academicYearId ? academic_years::find($academicYearId)?->year_label : null;

        $students = EloquentCollection::make($students->all());

        $students->loadMissing([
            'enrollments' => fn ($q) => $q->where('academic_year_id', $academicYearId)->with(['grade', 'section']),
        ]);

        $cards = $students->map(function (student $student) use ($yearLabel) {
            $enrollment = $student->enrollments->first();

            $classLabel = collect([$enrollment?->grade?->name, $enrollment?->section?->name])
                ->filter()
                ->implode(' - ');

            return [
                'type' => self::TYPE_STUDENT,
                'title' => 'بطاقة تعريف طالب',
                'title_en' => 'STUDENT ID CARD',
                'name' => $student->full_name,
                'id' => (string) $student->id,
                'rows' => [
                    ['رقم الطالب', (string) $student->id],
                    ['الرقم الوطني', $student->national_id ?: '—'],
                    ['الصف / الشعبة', $classLabel ?: '—'],
                ],
                'footer' => $yearLabel ? 'العام الدراسي ' . $yearLabel : 'تاريخ الإصدار ' . now()->format('Y/m/d'),
            ];
        })->values()->all();

        return $this->render($cards, $layout, 'بطاقات الطلبة');
    }

    /**
     * @param  Collection<int, Employee>  $employees
     */
    public function employees(Collection $employees, string $layout = self::LAYOUT_A4): string
    {
        $employees = EloquentCollection::make($employees->all());
        $employees->loadMissing(['employeeType']);

        $cards = $employees->map(fn (Employee $employee) => [
            'type' => self::TYPE_EMPLOYEE,
            'title' => 'بطاقة تعريف موظف',
            'title_en' => 'STAFF ID CARD',
            'name' => $employee->name,
            'id' => (string) $employee->id,
            'rows' => [
                ['الرقم الوظيفي', (string) $employee->id],
                ['الوظيفة', $employee->employeeType?->type_name ?: '—'],
            ],
            'footer' => 'تاريخ الإصدار ' . now()->format('Y/m/d'),
        ])->values()->all();

        return $this->render($cards, $layout, 'بطاقات الموظفين');
    }

    public static function filename(string $type, int $count): string
    {
        $prefix = $type === self::TYPE_STUDENT ? 'بطاقات_الطلبة' : 'بطاقات_الموظفين';

        return $prefix . '_' . $count . '_' . now()->format('Y_m_d_His') . '.pdf';
    }

    private function render(array $cards, string $layout, string $title): string
    {
        @set_time_limit(300);

        $isCardLayout = $layout === self::LAYOUT_CARD;

        $pdf = $isCardLayout
            ? new TCPDF('L', 'mm', [self::CARD_W, self::CARD_H], true, 'UTF-8', false)
            : new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('نظام تيجان');
        $pdf->SetAuthor('نظام تيجان');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setCellPaddings(0, 0, 0, 0);

        $logo = $this->logoPath();

        if ($isCardLayout) {
            foreach ($cards as $card) {
                $pdf->AddPage('L', [self::CARD_W, self::CARD_H]);
                $this->drawCard($pdf, $card, 0, 0, $logo, false);
            }

            return $pdf->Output('', 'S');
        }

        $perPage = self::A4_COLS * self::A4_ROWS;
        $marginX = (210 - (self::A4_COLS * self::CARD_W) - ((self::A4_COLS - 1) * self::A4_GAP_X)) / 2;
        $marginY = (297 - (self::A4_ROWS * self::CARD_H) - ((self::A4_ROWS - 1) * self::A4_GAP_Y)) / 2;

        foreach (array_values($cards) as $index => $card) {
            $slot = $index % $perPage;

            if ($slot === 0) {
                $pdf->AddPage();
            }

            // الترتيب من اليمين إلى اليسار
            $col = self::A4_COLS - 1 - ($slot % self::A4_COLS);
            $row = intdiv($slot, self::A4_COLS);

            $x = $marginX + $col * (self::CARD_W + self::A4_GAP_X);
            $y = $marginY + $row * (self::CARD_H + self::A4_GAP_Y);

            $this->drawCard($pdf, $card, $x, $y, $logo, true);
        }

        return $pdf->Output('', 'S');
    }

    private function drawCard(TCPDF $pdf, array $card, float $x, float $y, ?string $logo, bool $withBorder): void
    {
        $w = self::CARD_W;
        $h = self::CARD_H;
        $radius = 3.0;
        $headerH = 14.0;
        $footerH = 6.0;
        $pad = 4.0;

        $headerColor = self::HEADER_COLORS[$card['type']] ?? self::COLOR_DARK;

        // خلفية البطاقة
        $pdf->RoundedRect($x, $y, $w, $h, $radius, '1111', 'F', [], [255, 255, 255]);

        // الشريط العلوي + خط ذهبي
        $pdf->RoundedRect($x, $y, $w, $headerH, $radius, '1001', 'F', [], $headerColor);
        $pdf->Rect($x, $y + $headerH, $w, 0.8, 'F', [], self::COLOR_GOLD);

        // الشعار داخل مربع أبيض على اليمين
        $logoBox = 11.0;
        $logoX = $x + $w - 2.5 - $logoBox;
        $logoY = $y + 1.5;
        $pdf->RoundedRect($logoX, $logoY, $logoBox, $logoBox, 1.5, '1111', 'F', [], [255, 255, 255]);
        if ($logo) {
            $pdf->Image($logo, $logoX + 0.5, $logoY + 0.5, $logoBox - 1, $logoBox - 1, 'PNG', '', '', true, 300, '', false, false, 0, true);
        }

        // اسم الجهة ونوع البطاقة
        $textRight = $logoX - 2.0;
        $textW = 48.0;
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont(self::FONT, 'B', 11);
        $pdf->SetXY($textRight - $textW, $y + 2.2);
        $pdf->Cell($textW, 5, (string) config('id_cards.institution_name'), 0, 0, 'R');

        $pdf->SetTextColor(...self::COLOR_GOLD);
        $pdf->SetFont(self::FONT, '', 8);
        $pdf->SetXY($textRight - $textW, $y + 7.6);
        $pdf->Cell($textW, 4, $card['title'], 0, 0, 'R');

        // العنوان اللاتيني على اليسار
        $pdf->SetFont(self::FONT_LATIN, 'B', 5);
        $pdf->SetTextColor(...self::COLOR_GOLD);
        $pdf->SetXY($x + $pad, $y + 5.2);
        $pdf->Cell(22, 3, $card['title_en'], 0, 0, 'L');

        // مربع فارغ لتدبيس الصورة الشخصية (نسبة 3:4 تقريباً)
        $photoW = 22.0;
        $photoH = 27.5;
        $photoX = $x + $pad;
        $photoY = $y + $headerH + 2.6;
        $pdf->RoundedRect($photoX, $photoY, $photoW, $photoH, 1.2, '1111', 'DF', [
            'width' => 0.3,
            'dash' => '1,1',
            'color' => self::COLOR_GOLD,
        ], [252, 250, 246]);

        $pdf->SetFont(self::FONT, '', 9);
        $pdf->SetTextColor(...self::COLOR_MUTED);
        $pdf->SetXY($photoX, $photoY + ($photoH - 5) / 2);
        $pdf->Cell($photoW, 5, 'الصورة', 0, 0, 'C');
        $pdf->SetLineStyle(['width' => 0.2, 'dash' => 0]);

        // الاسم
        $infoLeft = $photoX + $photoW + 4.0;
        $infoRight = $x + $w - $pad;
        $infoW = $infoRight - $infoLeft;

        $nameY = $y + $headerH + 2.6;
        $this->isLatin($card['name'])
            ? $this->fitFont($pdf, $card['name'], 'B', 10, 6.5, $infoW, self::FONT_LATIN)
            : $this->fitFont($pdf, $card['name'], 'B', 11, 7, $infoW);
        $pdf->SetTextColor(...self::COLOR_TEXT);
        $pdf->SetXY($infoLeft, $nameY);
        $pdf->Cell($infoW, 6, $card['name'], 0, 0, 'R');

        $pdf->Rect($infoRight - 18, $nameY + 6.6, 18, 0.5, 'F', [], self::COLOR_GOLD);

        // صفوف البيانات
        $labelW = 19.0;
        $rowY = $nameY + 8.4;
        $rowH = 5.2;

        foreach ($card['rows'] as [$label, $value]) {
            $pdf->SetFont(self::FONT, '', 7);
            $pdf->SetTextColor(...self::COLOR_MUTED);
            $pdf->SetXY($infoRight - $labelW, $rowY);
            $pdf->Cell($labelW, $rowH, $label . ':', 0, 0, 'R');

            $valueW = $infoW - $labelW - 1;
            $this->isLatin($value)
                ? $this->fitFont($pdf, $value, 'B', 7.5, 5, $valueW, self::FONT_LATIN)
                : $this->fitFont($pdf, $value, 'B', 8, 5.5, $valueW);
            $pdf->SetTextColor(...self::COLOR_TEXT);
            $pdf->SetXY($infoLeft, $rowY);
            $pdf->Cell($valueW, $rowH, $value, 0, 0, 'R');

            $rowY += $rowH;
        }

        // الشريط السفلي
        $footerY = $y + $h - $footerH;
        $pdf->RoundedRect($x, $footerY, $w, $footerH, $radius, '0110', 'F', [], self::COLOR_GOLD_LIGHT);
        $pdf->Rect($x, $footerY, $w, 0.3, 'F', [], self::COLOR_GOLD);

        $pdf->SetFont(self::FONT, 'B', 7);
        $pdf->SetTextColor(...$headerColor);
        $pdf->SetXY($x + $w / 2, $footerY + 1);
        $pdf->Cell($w / 2 - $pad, 4, $card['footer'], 0, 0, 'R');

        $note = (string) config('id_cards.footer_note');
        if ($note !== '') {
            $noteW = $w / 2 - $pad;
            $this->fitFont($pdf, $note, '', 5.5, 4, $noteW);
            $pdf->SetTextColor(...self::COLOR_MUTED);
            $pdf->SetXY($x + $pad, $footerY + 1);
            $pdf->Cell($noteW, 4, $note, 0, 0, 'L');
        }

        // إطار القص (في تخطيط A4 فقط)
        if ($withBorder) {
            $pdf->RoundedRect($x, $y, $w, $h, $radius, '1111', 'D', [
                'width' => 0.2,
                'color' => self::COLOR_BORDER,
            ]);
        }
    }

    /**
     * تصغير الخط تدريجياً حتى يتسع النص للعرض المتاح.
     */
    private function fitFont(TCPDF $pdf, string $text, string $style, float $max, float $min, float $width, string $font = self::FONT): void
    {
        $size = $max;
        $pdf->SetFont($font, $style, $size);

        while ($size > $min && $pdf->GetStringWidth($text, $font, $style, $size) > $width) {
            $size -= 0.25;
            $pdf->SetFont($font, $style, $size);
        }
    }

    /**
     * الخط العربي يعرض الأرقام والحروف اللاتينية بشكل رفيع، فنستخدم خطاً لاتينياً لها.
     */
    private function isLatin(string $text): bool
    {
        return (bool) preg_match('/^[ -~]+$/', $text);
    }

    /**
     * الشعار بصيغة PNG شفافة يحتاج إضافة GD أو Imagick في TCPDF.
     */
    private function logoPath(): ?string
    {
        $path = config('id_cards.logo_path');

        if (! $path || ! is_file($path)) {
            return null;
        }

        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            return null;
        }

        return $path;
    }
}
