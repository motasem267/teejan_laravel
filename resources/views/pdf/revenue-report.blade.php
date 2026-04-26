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
            font-family: DejaVu Sans, serif;
            direction: rtl;
            text-align: right;
            color: #1f2937;
            font-size: 13px;
            background: #ffffff;
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
            font-size: 12px;
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

        .percentage {
            font-weight: bold;
            text-align: center;
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
            margin: 10px 0;
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

        .status-paid {
            color: #059669;
            font-weight: bold;
        }

        .status-pending {
            color: #dc2626;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">نظام إدارة تيجان العلم</div>
        <div class="report-title">تقرير الأقساط والإيرادات</div>
        <div class="report-date">
            السنة الدراسية: {{ $year ?? 'جميع السنوات' }}<br>
            تاريخ الطباعة: {{ date('Y-m-d H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ولي الأمر</th>
                <th>الجوال</th>
                <th>الإجمالي المستحق</th>
                <th>المبلغ المدفوع</th>
                <th>المتبقي</th>
                <th>نسبة الدفع</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            <tr>
                <td>{{ $record['parent_name'] ?? '-' }}</td>
                <td class="text-center">{{ $record['parent_phone'] ?? '-' }}</td>
                <td class="amount">{{ number_format($record['total_amount'], 2) }}</td>
                <td class="amount" style="color: #059669;">{{ number_format($record['paid_amount'], 2) }}</td>
                <td class="amount" style="color: {{ $record['remaining_amount'] > 0 ? '#dc2626' : '#059669' }};">
                    {{ number_format($record['remaining_amount'], 2) }}
                </td>
                <td class="percentage">{{ number_format($record['payment_percentage'], 1) }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">لا توجد بيانات</td>
            </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2" class="text-center">الإجمالي:</td>
                <td class="amount">{{ number_format($totalAmount, 2) }} ريال</td>
                <td class="amount" style="color: #059669;">{{ number_format($paidAmount, 2) }} ريال</td>
                <td class="amount" style="color: {{ $remainingAmount > 0 ? '#dc2626' : '#059669' }};">
                    {{ number_format($remainingAmount, 2) }} ريال
                </td>
                <td class="percentage">{{ number_format($totalAmount > 0 ? ($paidAmount / $totalAmount * 100) : 0, 1) }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-item">
            <span>الإجمالي المستحق:</span>
            <strong>{{ number_format($totalAmount, 2) }} ريال</strong>
        </div>
        <div class="summary-item">
            <span style="color: #059669;">المتحصل:</span>
            <strong style="color: #059669;">{{ number_format($paidAmount, 2) }} ريال</strong>
        </div>
        <div class="summary-item">
            <span style="color: #dc2626;">المتبقي:</span>
            <strong style="color: #dc2626;">{{ number_format($remainingAmount, 2) }} ريال</strong>
        </div>
        <div class="summary-item" style="margin-top: 15px; border-top: 1px solid #0284c7; padding-top: 10px;">
            <span>نسبة التحصيل الكلية:</span>
            <strong>{{ number_format($totalAmount > 0 ? ($paidAmount / $totalAmount * 100) : 0, 2) }}%</strong>
        </div>
        <div class="summary-item">
            <span>عدد أولياء الأمور:</span>
            <strong>{{ count($records) }}</strong>
        </div>
    </div>

    <div class="footer">
        تم إنشاء هذا التقرير تلقائياً من نظام إدارة تيجان العلم
    </div>
</body>
</html>
