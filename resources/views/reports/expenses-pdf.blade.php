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
        }

        thead {
            background: #475569;
            color: #ffffff;
        }

        th {
            padding: 12px 8px;
            text-align: right;
            border: 1px solid #cbd5e1;
            font-weight: bold;
            font-size: 13px;
        }

        td {
            padding: 10px 8px;
            border: 1px solid #e2e8f0;
            font-size: 12px;
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
            font-size: 13px;
        }

        .total-row td {
            border-top: 2px solid #475569;
            border-bottom: 2px solid #475569;
            padding: 12px 8px;
        }

        .summary-box {
            margin-top: 30px;
            padding: 15px;
            background: #f0f9ff;
            border: 2px solid #0284c7;
            border-radius: 6px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 13px;
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
        <div class="report-title">تقرير إجمالي المصاريف</div>
        <div class="report-date">
            من: {{ $startDate ?? 'غير محدد' }} إلى: {{ $endDate ?? 'غير محدد' }}<br>
            تاريخ الطباعة: {{ date('Y-m-d H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>نوع المصروف</th>
                <th>الوصف</th>
                <th>المبلغ</th>
                <th>طريقة الدفع</th>
                <th>المسجل بواسطة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td class="text-center">{{ $record->date->format('Y-m-d') }}</td>
                <td>{{ $record->expensesType?->expenses_type_name ?? '-' }}</td>
                <td>{{ $record->description }}</td>
                <td class="amount">{{ number_format($record->amount, 2) }}</td>
                <td>{{ $record->paymentMethod?->payment_type ?? '-' }}</td>
                <td>{{ $record->creator?->name ?? '-' }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" class="text-center">الإجمالي:</td>
                <td class="amount">{{ number_format($total, 2) }} ريال</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-item">
            <span>إجمالي المصاريف:</span>
            <strong>{{ number_format($total, 2) }} ريال</strong>
        </div>
        <div class="summary-item">
            <span>عدد التسجيلات:</span>
            <strong>{{ count($records) }}</strong>
        </div>
        <div class="summary-item">
            <span>المتوسط:</span>
            <strong>{{ count($records) > 0 ? number_format($total / count($records), 2) : 0 }} ريال</strong>
        </div>
    </div>

    <div class="footer">
        تم إنشاء هذا التقرير تلقائياً من نظام إدارة تيجان العلم
    </div>
</body>
</html>
