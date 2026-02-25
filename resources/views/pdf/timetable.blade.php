<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>الجدول الدراسي</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}');
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            direction: rtl;
            font-size: 11px;
            margin: 10px;
            color: #1a1a1a;
        }

        .page-break {
            page-break-after: always;
        }

        .schedule-block {
            margin-bottom: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px 0;
            color: #1e3a5f;
        }

        .header p {
            font-size: 12px;
            margin: 0;
            color: #555;
        }

        .timetable {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .timetable th,
        .timetable td {
            border: 1px solid #999;
            padding: 5px 6px;
            text-align: center;
            vertical-align: middle;
        }

        .timetable thead th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
        }

        .timetable thead th .period-time {
            font-size: 9px;
            font-weight: normal;
            color: #cce;
            display: block;
            margin-top: 2px;
        }

        .timetable tbody tr:nth-child(even) {
            background-color: #f5f8ff;
        }

        .day-cell {
            background-color: #2c5282;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            width: 60px;
        }

        .schedule-cell-subject {
            font-weight: bold;
            font-size: 10px;
            color: #1a365d;
            display: block;
        }

        .schedule-cell-teacher {
            font-size: 9px;
            color: #555;
            display: block;
            margin-top: 2px;
        }

        .empty-cell {
            color: #ccc;
            font-size: 9px;
        }
    </style>
</head>
<body>
    @foreach($classes as $classData)
        @php
            $class   = $classData['class'];
            $grid    = $classData['grid'];
            $days    = $classData['days'];
            $periods = $classData['lessonTimes'];
            $yearLabel = $classData['yearLabel'] ?? '';
            $gradeName   = $class->grade?->name ?? '';
            $sectionName = $class->section?->name ?? '';
        @endphp

        <div class="schedule-block">
            <div class="header">
                <h2>الجدول الدراسي — {{ $gradeName }} - {{ $sectionName }}</h2>
                @if($yearLabel)
                    <p>السنة الدراسية: {{ $yearLabel }}</p>
                @endif
            </div>

            <table class="timetable">
                <thead>
                    <tr>
                        <th>اليوم</th>
                        @foreach($periods as $period)
                            <th>
                                الحصة {{ $period->period_number }}
                                <span class="period-time">
                                    {{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $day)
                        <tr>
                            <td class="day-cell">{{ $day->day_name_ar }}</td>
                            @foreach($periods as $period)
                                @php
                                    $entry = $grid[$day->id][$period->id] ?? null;
                                @endphp
                                <td>
                                    @if($entry)
                                        <span class="schedule-cell-subject">{{ $entry['subject'] }}</span>
                                        <span class="schedule-cell-teacher">{{ $entry['teacher'] }}</span>
                                    @else
                                        <span class="empty-cell">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
