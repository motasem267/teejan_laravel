<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
   <style>
    @page {
        margin: 20mm;
    }

    body {
        font-family: 'dejavusans', sans-serif;
        direction: rtl;
        text-align: right;
        color: #1f2937;
        font-size: 14px;
        background: #ffffff;
    }

    /* ===== Header ===== */

    .header {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
    }

    .school-name {
        font-size: 22px;
        font-weight: bold;
        color: #0f172a;
    }

    .report-title {
        font-size: 17px;
        margin-top: 5px;
        font-weight: bold;
        color: #475569;
    }

    /* ===== Student Info Box ===== */

    .student-info {
        margin-bottom: 25px;
        line-height: 2;
        font-size: 14px;
        background: #f8fafc;
        padding: 15px 18px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .student-info span {
        display: inline-block;
        min-width: 220px;
        color: #334155;
    }

    .student-info strong {
        color: #0f172a;
    }

    /* ===== Table ===== */

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 13px;
    }

    th, td {
        border: 1px solid #d1d5db;
        padding: 9px;
        text-align: center;
    }

    th {
        background: #e2e8f0;
        font-weight: bold;
        color: #1e293b;
    }

    tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    tbody tr:hover {
        background: #f1f5f9;
    }

    td.question-cell {
        text-align: right;
        font-weight: 600;
        color: #1f2937;
    }

    /* ===== Badge ===== */

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        background: #dbeafe;
        border: 1px solid #93c5fd;
        font-size: 12px;
        font-weight: bold;
        color: #1d4ed8;
    }

    /* ===== No Data ===== */

    .no-data {
        text-align: center;
        padding: 25px;
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 8px;
        margin-top: 20px;
        color: #475569;
        font-weight: 600;
    }
</style>

</head>

<body>

@forelse($groupedEvaluations as $evaluations)

    @php
        $firstEval = is_array($evaluations) ? reset($evaluations) : $evaluations->first();
        if (!$firstEval) continue;

        $studentName = $firstEval->student->full_name ?? '-';

        $monthValue = $firstEval->month ?? null;
        $weekValue = $firstEval->week ?? null;
        $yearLabel = $firstEval->academicYear->year_label ?? '-';

        $monthName = (isset($monthValue) && is_numeric($monthValue) && isset($months[(int)$monthValue]))
            ? $months[(int)$monthValue] : ($monthValue ?? '-');

        $weekName = (isset($weekValue) && is_numeric($weekValue) && isset($weeks[(int)$weekValue]))
            ? $weeks[(int)$weekValue] : ($weekValue ?? '-');
    @endphp

    <!-- Header -->
    <div class="header">
        <div class="school-name">مدرسة تيجان العلم</div>
        <div class="report-title">تقرير تقييم أسبوعي</div>
    </div>

    <!-- Student Info -->
    <div class="student-info">
        <span><strong>الطالب:</strong> {{ $cleanText($studentName) }}</span>
        <span><strong>السنة الدراسية:</strong> {{ $yearLabel }}</span><br>
        <span><strong>الشهر:</strong> {{ $monthName }}</span>
        <span><strong>الأسبوع:</strong> {{ $weekName }}</span>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>المعيار التعليمي والتربوي</th>
                <th>مستوى الأداء</th>
                <th>المعلم المقيّم</th>
            </tr>
        </thead>
        <tbody>

        @foreach($evaluations as $index => $evaluation)
            <tr>
                <td>{{ $index + 1 }}</td>

                <td class="question-cell">
                    {{ $cleanText($evaluation->question->label ?? '-') }}
                </td>

                <td>
                    <span class="badge">
                        {{ isset($evaluation->answer->label) ? $cleanText($evaluation->answer->label) : 'غير محدد' }}
                    </span>
                </td>

                <td>
                    {{ $cleanText($evaluation->teacher->name ?? 'غير محدد') }}
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>

@empty
    <div class="no-data">
        لا توجد تقييمات مسجلة.
    </div>
@endforelse

</body>
</html>
