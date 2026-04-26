<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        @page {
            margin: 20mm;
        }

        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            text-align: right;
            color: #1f2937;
            font-size: 13px;
            background: #ffffff;
            word-wrap: break-word;
        }

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
            font-size: 16px;
            margin-top: 5px;
            font-weight: bold;
            color: #475569;
        }

        .report-date {
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #ffffff;
            font-size: 11px;
        }

        thead {
            background: #475569;
            color: #ffffff;
        }

        th {
            padding: 10px 6px;
            text-align: right;
            border: 1px solid #cbd5e1;
            font-weight: bold;
            font-size: 11px;
        }

        td {
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:hover {
            background: #f1f5f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount {
            font-weight: bold;
            color: #0f172a;
            text-align: left;
        }

        .total-row {
            background: #e2e8f0;
            font-weight: bold;
            font-size: 12px;
        }

        .total-row td {
            border-top: 2px solid #475569;
            border-bottom: 2px solid #475569;
            padding: 10px 6px;
        }

        .summary-box {
            margin-top: 30px;
            padding: 15px;
            background: #f0f9ff;
            border: 2px solid #0284c7;
            border-radius: 6px;
            font-size: 12px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }

        .summary-item strong {
            margin-left: 20px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">نظام إدارة تيجان العلم</div>
        <div class="report-title">تقرير صرف الرواتب</div>
        <div class="report-date">
            الشهر: {{ $month ? ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'][$month - 1] : 'جميع الأشهر' }} 
            | السنة: {{ $year ?? 'جميع السنوات' }}<br>
            تاريخ الطباعة: {{ date('Y-m-d H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>الموظف</th>
                <th>الشهر</th>
                <th>السنة</th>
                <th>الأساسي</th>
                <th>الحوافز</th>
                <th>الخصومات</th>
                <th>الصافي</th>
                <th>الحصص</th>
                <th>الأيام</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ $record->employee?->name ?? '-' }}</td>
                <td class="text-center">{{ $record->month }}</td>
                <td class="text-center">{{ $record->year }}</td>
                <td class="amount">{{ number_format($record->basic_salary, 2) }}</td>
                <td class="amount">{{ number_format($record->bonus_amount, 2) }}</td>
                <td class="amount">{{ number_format($record->deduction_amount, 2) }}</td>
                <td class="amount">{{ number_format($record->net_salary, 2) }}</td>
                <td class="text-center">{{ $record->sessions_count }}</td>
                <td class="text-center">{{ $record->attendance_days }}</td>
                <td class="text-center">{{ $record->payment_date->format('Y-m-d') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-center">الإجمالي:</td>
                <td class="amount">{{ number_format($totalBasic, 2) }}</td>
                <td class="amount">{{ number_format($totalBonus, 2) }}</td>
                <td class="amount">{{ number_format($totalDeduction, 2) }}</td>
                <td class="amount">{{ number_format($totalNet, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-item">
            <span>إجمالي الرواتب الأساسية:</span>
            <strong>{{ number_format($totalBasic, 2) }} ريال</strong>
        </div>
        <div class="summary-item">
            <span>إجمالي الحوافز:</span>
            <strong>{{ number_format($totalBonus, 2) }} ريال</strong>
        </div>
        <div class="summary-item">
            <span>إجمالي الخصومات:</span>
            <strong>{{ number_format($totalDeduction, 2) }} ريال</strong>
        </div>
        <div class="summary-item" style="margin-top: 15px; border-top: 1px solid #0284c7; padding-top: 10px;">
            <span>إجمالي الرواتب الصافية:</span>
            <strong>{{ number_format($totalNet, 2) }} ريال</strong>
        </div>
        <div class="summary-item">
            <span>عدد الموظفين:</span>
            <strong>{{ count($records) }}</strong>
        </div>
    </div>

    <div class="footer">
        تم إنشاء هذا التقرير تلقائياً من نظام إدارة تيجان العلم
    </div>
</body>
</html>
