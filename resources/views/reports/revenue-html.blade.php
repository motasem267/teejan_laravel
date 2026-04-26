<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الإيرادات</title>
    <style>
        @page {
            margin: 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'dejavusans', sans-serif;
        }

        body {
            font-family: 'dejavusans', sans-serif;
            direction: rtl;
            text-align: right;
            color: #1f2937;
            font-size: 13px;
            line-height: 1.6;
            background: #ffffff;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            margin-top: 10px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0f172a;
        }

        .school-name {
            font-size: 28px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 22px;
            color: #475569;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .report-date {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .report-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #475569;
        }

        .info-value {
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            font-size: 12px;
        }

        thead {
            background: #0f172a;
            color: white;
        }

        th {
            padding: 12px;
            text-align: right;
            font-weight: 600;
            font-size: 12px;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        td {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            font-size: 12px;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:hover {
            background: #f1f5f9;
        }

        .amount {
            text-align: left;
            font-weight: 600;
            color: #0f172a;
        }

        .percentage {
            text-align: center;
            font-weight: 600;
            color: #0f172a;
        }

        .total-row {
            background: #0f172a;
            color: white;
            font-weight: 600;
        }

        .total-row td {
            border-color: #0f172a;
        }

        .summary-box {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .summary-card h3 {
            font-size: 12px;
            margin-bottom: 10px;
            opacity: 0.9;
            font-weight: 500;
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            color: #64748b;
            font-size: 12px;
        }

        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            background: #0f172a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #1e293b;
        }

        @media print {
            body {
                background: white;
            }

            .container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .print-button {
                display: none;
            }

            .footer {
                page-break-inside: avoid;
            }

            @page {
                margin: 10mm;
                size: A4 landscape;
            }

            table {
                font-size: 11px;
            }

            th, td {
                padding: 8px;
                font-size: 11px;
            }
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <div class="school-name">نظام إدارة تيجان العلم</div>
            <div class="report-title">تقرير الأقساط والإيرادات</div>
            <div class="report-date">
                تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}
            </div>
        </div>

        @if($records->count() > 0)
            <div class="report-info">
                <div class="info-item">
                    <span class="info-label">عدد الآباء:</span>
                    <span class="info-value">{{ $records->count() }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">إجمالي الإيرادات:</span>
                    <span class="info-value">{{ number_format($totalAmount, 2) }} دينار</span>
                </div>
                <div class="info-item">
                    <span class="info-label">المبلغ المحصل:</span>
                    <span class="info-value">{{ number_format($paidAmount, 2) }} دينار</span>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الأب</th>
                        <th>رقم الهاتف</th>
                        <th>المبلغ المستحق</th>
                        <th>المبلغ المدفوع</th>
                        <th>المتبقي</th>
                        <th>نسبة الدفع %</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @foreach($records as $record)
                        <tr>
                            <td>{{ $counter++ }}</td>
                            <td>{{ $record['name'] ?? '-' }}</td>
                            <td>{{ $record['phone'] ?? '-' }}</td>
                            <td class="amount">{{ number_format($record['total_amount'], 2) }}</td>
                            <td class="amount">{{ number_format($record['paid_amount'], 2) }}</td>
                            <td class="amount">{{ number_format($record['remaining_amount'], 2) }}</td>
                            <td class="percentage">{{ number_format($record['payment_percentage'], 1) }}%</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3" style="text-align: center;">الإجمالي:</td>
                        <td class="amount">{{ number_format($totalAmount, 2) }}</td>
                        <td class="amount">{{ number_format($paidAmount, 2) }}</td>
                        <td class="amount">{{ number_format($remainingAmount, 2) }}</td>
                        <td class="percentage">{{ $totalAmount > 0 ? number_format(($paidAmount / $totalAmount) * 100, 1) : 0 }}%</td>
                    </tr>
                </tbody>
            </table>

            <div class="summary-box">
                <div class="summary-card">
                    <h3>إجمالي الإيرادات</h3>
                    <div class="value">{{ number_format($totalAmount, 0) }}</div>
                </div>
                <div class="summary-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <h3>المحصل</h3>
                    <div class="value">{{ number_format($paidAmount, 0) }}</div>
                </div>
                <div class="summary-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <h3>المتبقي</h3>
                    <div class="value">{{ number_format($remainingAmount, 0) }}</div>
                </div>
                <div class="summary-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <h3>نسبة الجمع</h3>
                    <div class="value">{{ $totalAmount > 0 ? number_format(($paidAmount / $totalAmount) * 100, 0) : 0 }}%</div>
                </div>
            </div>
        @else
            <div class="no-data">
                <p>لا توجد بيانات إيرادات</p>
            </div>
        @endif

        <div class="footer">
            <p>تم إنشاء هذا التقرير من نظام إدارة تيجان العلم</p>
        </div>
    </div>
</body>
</html>
