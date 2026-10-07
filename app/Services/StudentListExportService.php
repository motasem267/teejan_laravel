<?php

namespace App\Services;

use App\Models\StudentEnrollment;
use App\Models\student;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use TCPDF;

/**
 * استخراج قوائم الطلبة بأعمدة يختارها المستخدم (PDF أو Excel).
 *
 * كل مجموعة (group) هي قائمة مستقلة: ['title' => 'صف أول - A', 'rows' => [[student, enrollment], ...]]
 */
class StudentListExportService
{
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_XLSX = 'xlsx';

    private const FONT = 'aealarabiya';
    private const FONT_LATIN = 'dejavusans';

    /**
     * الأعمدة المتاحة: المفتاح => [العنوان، العرض النسبي]
     */
    public const COLUMNS = [
        'serial' => ['م', 4],
        'id' => ['رقم الطالب', 10],
        'full_name' => ['اسم الطالب', 26],
        'national_id' => ['الرقم الوطني', 15],
        'grade' => ['الصف', 11],
        'section' => ['الشعبة', 7],
        'parent_name' => ['ولي الأمر', 18],
        'parent_phone' => ['هاتف ولي الأمر', 12],
        'mother_phone' => ['هاتف الأم', 12],
        'parent_address' => ['العنوان', 16],
        'status' => ['الحالة', 8],
    ];

    public const DEFAULT_COLUMNS = ['serial', 'full_name', 'national_id'];

    private const BLANK_COLUMN_WIDTH = 11;

    public static function columnOptions(): array
    {
        return collect(self::COLUMNS)->map(fn ($c) => $c[0])->all();
    }

    /**
     * @param  array<int, array{title: string, rows: Collection}>  $groups
     * @param  array<int, string>  $columns  مفاتيح من COLUMNS
     * @param  array<int, string>  $blankColumns  عناوين أعمدة فارغة إضافية
     */
    public function pdf(array $groups, array $columns, array $blankColumns, array $meta, bool $landscape): string
    {
        @set_time_limit(300);

        $pdf = new TCPDF($landscape ? 'L' : 'P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('نظام تيجان');
        $pdf->SetAuthor('نظام تيجان');
        $pdf->SetTitle($meta['title']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterFont([self::FONT_LATIN, '', 8]);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetFooterMargin(8);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->setRTL(true);

        $headers = $this->headers($columns, $blankColumns);
        $widths = $this->widths($columns, $blankColumns);

        foreach ($groups as $group) {
            $pdf->AddPage();
            $pdf->writeHTML($this->groupHtml($group, $columns, $blankColumns, $headers, $widths, $meta), true, false, true, false, '');
        }

        return $pdf->Output('', 'S');
    }

    /**
     * يكتب ملف Excel مؤقت ويعيد مساره. كل مجموعة في ورقة مستقلة.
     */
    public function xlsx(array $groups, array $columns, array $blankColumns, array $meta): string
    {
        $path = tempnam(sys_get_temp_dir(), 'students_') . '.xlsx';

        $writer = new Writer;
        $writer->openToFile($path);

        $border = new Border(
            new BorderPart(Border::TOP, '999999', Border::WIDTH_THIN),
            new BorderPart(Border::BOTTOM, '999999', Border::WIDTH_THIN),
            new BorderPart(Border::LEFT, '999999', Border::WIDTH_THIN),
            new BorderPart(Border::RIGHT, '999999', Border::WIDTH_THIN),
        );
        $titleStyle = (new Style)->setFontBold()->setFontSize(14);
        $subtitleStyle = (new Style)->setFontSize(11)->setFontColor('555555');
        $headerStyle = (new Style)->setFontBold()->setFontColor('FFFFFF')->setBackgroundColor('212124')
            ->setCellAlignment(CellAlignment::CENTER)->setBorder($border);
        $cellStyle = (new Style)->setBorder($border);

        $headers = $this->headers($columns, $blankColumns);
        $widths = $this->widths($columns, $blankColumns);
        $usedNames = [];

        foreach (array_values($groups) as $index => $group) {
            $sheet = $index === 0 ? $writer->getCurrentSheet() : $writer->addNewSheetAndMakeItCurrent();
            $sheet->setName($this->sheetName($group['title'], $usedNames));
            $sheet->setSheetView((new SheetView)->setRightToLeft(true));

            foreach ($widths as $i => $width) {
                $sheet->setColumnWidth(max(6, $width * 1.1), $i + 1);
            }

            $writer->addRow(Row::fromValues([$meta['title']], $titleStyle));
            $writer->addRow(Row::fromValues([$this->subtitle($group, $meta)], $subtitleStyle));
            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues($headers, $headerStyle));

            foreach ($group['rows']->values() as $i => [$student, $enrollment]) {
                $values = [];
                foreach ($columns as $key) {
                    $values[] = $this->value($key, $student, $enrollment, $i + 1);
                }
                foreach ($blankColumns as $_) {
                    $values[] = '';
                }
                $writer->addRow(Row::fromValues($values, $cellStyle));
            }
        }

        $writer->close();

        return $path;
    }

    public static function filename(string $format): string
    {
        return 'قوائم_الطلبة_' . now()->format('Y_m_d_His') . '.' . $format;
    }

    private function groupHtml(array $group, array $columns, array $blankColumns, array $headers, array $widths, array $meta): string
    {
        $institution = e((string) config('id_cards.institution_name'));
        $title = e($meta['title']);
        $subtitle = e($this->subtitle($group, $meta));
        $total = array_sum($widths);

        $html = '<table cellpadding="2"><tr>'
            . '<td width="100%" align="center"><span style="font-family:' . self::FONT . ';font-size:15pt;font-weight:bold;color:#5c3e1a;">' . $institution . '</span><br/>'
            . '<span style="font-family:' . self::FONT . ';font-size:13pt;font-weight:bold;color:#212124;">' . $title . '</span><br/>'
            . '<span style="font-family:' . self::FONT . ';font-size:10pt;color:#555555;">' . $subtitle . '</span></td>'
            . '</tr></table><br/>';

        $html .= '<table border="0.3" cellpadding="4" style="border-color:#999999;">';
        $html .= '<thead><tr style="background-color:#212124;color:#ffffff;">';
        foreach ($headers as $i => $header) {
            $w = round($widths[$i] / $total * 100, 2);
            $html .= '<th width="' . $w . '%" align="center" style="font-family:' . self::FONT . ';font-weight:bold;font-size:10pt;">' . $this->cellText($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($group['rows']->values() as $i => [$student, $enrollment]) {
            $bg = $i % 2 === 1 ? '#faf3e7' : '#ffffff';
            $html .= '<tr style="background-color:' . $bg . ';" nobr="true">';

            foreach ($columns as $c => $key) {
                $w = round($widths[$c] / $total * 100, 2);
                $value = (string) $this->value($key, $student, $enrollment, $i + 1);
                $font = preg_match('/^[\x20-\x7E]*$/', $value) ? self::FONT_LATIN : self::FONT;
                $size = $font === self::FONT_LATIN ? '8pt' : '10pt';
                $align = in_array($key, ['full_name', 'parent_name', 'parent_address'], true) ? 'right' : 'center';
                $html .= '<td width="' . $w . '%" align="' . $align . '" style="font-family:' . $font . ';font-size:' . $size . ';">' . $this->cellText($value) . '</td>';
            }

            foreach ($blankColumns as $b => $_) {
                $w = round($widths[count($columns) + $b] / $total * 100, 2);
                $html .= '<td width="' . $w . '%"></td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * TCPDF يُسقط النص العربي المكوّن من حرف واحد (مثل "م" أو شعبة "أ")،
     * فنحيط النص بمسافات غير قابلة للكسر.
     */
    private function cellText(string $text): string
    {
        return $text === '' ? '' : '&nbsp;' . e($text) . '&nbsp;';
    }

    private function subtitle(array $group, array $meta): string
    {
        return collect([
            $group['title'],
            $meta['year'] ? 'العام الدراسي ' . $meta['year'] : null,
            'عدد الطلبة: ' . $group['rows']->count(),
        ])->filter()->implode('   |   ');
    }

    private function headers(array $columns, array $blankColumns): array
    {
        return array_merge(
            array_map(fn ($key) => self::COLUMNS[$key][0], $columns),
            array_values($blankColumns),
        );
    }

    private function widths(array $columns, array $blankColumns): array
    {
        return array_merge(
            array_map(fn ($key) => self::COLUMNS[$key][1], $columns),
            array_fill(0, count($blankColumns), self::BLANK_COLUMN_WIDTH),
        );
    }

    private function value(string $key, student $student, ?StudentEnrollment $enrollment, int $serial): string|int
    {
        return match ($key) {
            'serial' => $serial,
            'id' => (string) $student->id,
            'full_name' => (string) $student->full_name,
            'national_id' => (string) ($student->national_id ?? ''),
            'grade' => (string) ($enrollment?->grade?->name ?? ''),
            'section' => (string) ($enrollment?->section?->name ?? ''),
            'parent_name' => (string) ($student->parent?->name ?? ''),
            'parent_phone' => (string) ($student->parent?->phone ?? ''),
            'mother_phone' => (string) ($student->mother_phone ?? ''),
            'parent_address' => (string) ($student->parent?->address ?? ''),
            'status' => (string) ($student->status?->name ?? ''),
            default => '',
        };
    }

    /**
     * أسماء أوراق Excel: 31 حرفاً كحد أقصى، بدون رموز ممنوعة، وغير مكررة.
     */
    private function sheetName(string $title, array &$used): string
    {
        $name = trim(mb_substr(str_replace(['\\', '/', '?', '*', '[', ']', ':'], '-', $title), 0, 28)) ?: 'قائمة';
        $candidate = $name;
        $n = 2;

        while (in_array($candidate, $used, true)) {
            $candidate = $name . ' ' . $n++;
        }

        $used[] = $candidate;

        return $candidate;
    }
}
