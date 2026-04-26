<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Salary;
use App\Models\ParentModel;
use App\Models\Installment;
use Illuminate\Support\Facades\Auth;
use TCPDF;

class ReportController extends Controller
{
    public function downloadExpensesPdf()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission') || !$user->hasPermission('expenses-report.view')) {
            abort(403, 'غير مصرح بالوصول');
        }

        $startDate = request('startDate');
        $endDate = request('endDate');

        $records = Expense::query()
            ->when($startDate, fn ($query) => $query->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('date', '<=', $endDate))
            ->get();

        $total = $records->sum('amount');

        try {
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('نظام تيجان');
            $pdf->SetAuthor('نظام تيجان');
            $pdf->SetTitle('تقرير المصاريف');
            $pdf->SetSubject('تقرير المصاريف');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->setRTL(true);

            // Title
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->SetTextColor(30, 58, 95);
            $pdf->Cell(0, 8, 'تقرير المصاريف', 0, 1, 'C');
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, 'من: ' . ($startDate ?? 'البداية') . ' إلى: ' . ($endDate ?? 'النهاية'), 0, 1, 'C');
            $pdf->Ln(8);

            // Table Header
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->SetFillColor(30, 58, 95);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(40, 7, 'التاريخ', 1, 0, 'C', true);
            $pdf->Cell(80, 7, 'الوصف', 1, 0, 'C', true);
            $pdf->Cell(40, 7, 'المبلغ', 1, 1, 'C', true);

            // Table Data
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            foreach ($records as $index => $record) {
                $bgColor = ($index % 2) ? [245, 245, 245] : [255, 255, 255];
                $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
                $pdf->Cell(40, 6, $record->date, 1, 0, 'C', true);
                $pdf->MultiCell(80, 6, $record->description ?? '', 1, 'R', true, 0, '', '', true, 0, false, true, 6, 'M');
                $pdf->Cell(40, 6, number_format($record->amount, 2), 1, 1, 'C', true);
            }

            // Total
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->SetFillColor(44, 82, 130);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(120, 7, 'الإجمالي', 1, 0, 'C', true);
            $pdf->Cell(40, 7, number_format($total, 2), 1, 1, 'C', true);

            $filename = 'تقرير_المصاريف_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $content = $pdf->Output($filename, 'S');

            return response()->streamDownload(
                fn () => print($content),
                $filename,
                ['Content-Type' => 'application/pdf'],
            );
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في توليد PDF: ' . $e->getMessage());
        }
    }

    public function downloadSalariesPdf()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission') || !$user->hasPermission('salaries-report.view')) {
            abort(403, 'غير مصرح بالوصول');
        }

        $month = request('month');
        $year = request('year');
        $employeeType = request('employeeType');

        $records = Salary::query()
            ->with(['employee', 'employee.employeeType', 'paymentMethod'])
            ->when($month, fn ($query) => $query->where('month', $month))
            ->when($year, fn ($query) => $query->where('year', $year))
            ->when($employeeType, fn ($query) => $query->whereHas('employee', function ($q) use ($employeeType) {
                $q->where('emp_type_id', $employeeType);
            }))
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalBasic = $records->sum('basic_salary');
        $totalBonus = $records->sum('bonus_amount');
        $totalDeduction = $records->sum('deduction_amount');
        $totalNet = $records->sum('net_salary');

        try {
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('نظام تيجان');
            $pdf->SetAuthor('نظام تيجان');
            $pdf->SetTitle('تقرير الرواتب');
            $pdf->SetSubject('تقرير الرواتب');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->setRTL(true);

            // Title
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->SetTextColor(30, 58, 95);
            $pdf->Cell(0, 8, 'تقرير الرواتب', 0, 1, 'C');
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, 'الشهر: ' . ($month ?? 'جميع الشهور') . ' | السنة: ' . ($year ?? 'جميع السنوات'), 0, 1, 'C');
            $pdf->Ln(8);

            // بناء جدول HTML
            $html = '<table border="1" cellpadding="3" cellspacing="0" style="width:100%; direction:rtl; text-align:right; font-size:7pt;">';
            
            // Header
            $html .= '<tr style="background-color:#1e3a5f; color:white; font-weight:bold;">';
            $html .= '<th>الاسم</th>';
            $html .= '<th>الشهر</th>';
            $html .= '<th>السنة</th>';
            $html .= '<th>الراتب الأساسي</th>';
            $html .= '<th>العلاوة</th>';
            $html .= '<th>الخصم</th>';
            $html .= '<th>الراتب الصافي</th>';
            $html .= '<th>حصص</th>';
            $html .= '<th>حضور</th>';
            $html .= '<th>الصرف</th>';
            $html .= '<th>الدفع</th>';
            $html .= '<th>ملاحظات</th>';
            $html .= '</tr>';
            
            // Data rows
            $months = [1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
                       7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'];
            
            foreach ($records as $index => $record) {
                $bgColor = ($index % 2) ? '#f5f5f5' : '#ffffff';
                $html .= '<tr style="background-color:' . $bgColor . ';">';
                $html .= '<td>' . htmlspecialchars($record->employee->name ?? '') . '</td>';
                $html .= '<td>' . ($months[$record->month] ?? '') . '</td>';
                $html .= '<td>' . $record->year . '</td>';
                $html .= '<td style="text-align:center;">' . number_format($record->basic_salary, 2) . '</td>';
                $html .= '<td style="text-align:center;">' . number_format($record->bonus_amount, 2) . '</td>';
                $html .= '<td style="text-align:center;">' . number_format($record->deduction_amount, 2) . '</td>';
                $html .= '<td style="text-align:center;">' . number_format($record->net_salary, 2) . '</td>';
                $html .= '<td style="text-align:center;">' . ($record->sessions_count ?? '-') . '</td>';
                $html .= '<td style="text-align:center;">' . ($record->attendance_days ?? '-') . '</td>';
                $html .= '<td>' . ($record->payment_date?->format('Y-m-d') ?? '-') . '</td>';
                $html .= '<td>' . ($record->paymentMethod?->payment_type ?? '-') . '</td>';
                $html .= '<td>' . htmlspecialchars($record->notes ?? '') . '</td>';
                $html .= '</tr>';
            }
            
            // Totals row
            $html .= '<tr style="background-color:#2c5282; color:white; font-weight:bold;">';
            $html .= '<td colspan="3">الإجمالي</td>';
            $html .= '<td style="text-align:center;">' . number_format($totalBasic, 2) . '</td>';
            $html .= '<td style="text-align:center;">' . number_format($totalBonus, 2) . '</td>';
            $html .= '<td style="text-align:center;">' . number_format($totalDeduction, 2) . '</td>';
            $html .= '<td style="text-align:center;">' . number_format($totalNet, 2) . '</td>';
            $html .= '<td colspan="5"></td>';
            $html .= '</tr>';
            
            $html .= '</table>';
            
            // طباعة الجدول باستخدام HTML
            $pdf->writeHTML($html, true, false, true, false, '');

            $filename = 'تقرير_الرواتب_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $content = $pdf->Output($filename, 'S');

            return response()->streamDownload(
                fn () => print($content),
                $filename,
                ['Content-Type' => 'application/pdf'],
            );
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في توليد PDF: ' . $e->getMessage());
        }
    }

    public function downloadRevenuePdf()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission') || !$user->hasPermission('revenue-report.view')) {
            abort(403, 'غير مصرح بالوصول');
        }

        $academicYear = request('year');

        $records = ParentModel::query()
            ->get()
            ->map(function ($parent) use ($academicYear) {
                $students = $parent->students;
                $totalAmount = 0;

                // احصل على المبلغ المستحق من رسوم الاشتراك السنوي
                foreach ($students as $student) {
                    $enrollment = $student->enrollments()
                        ->when($academicYear, function ($q) use ($academicYear) {
                            $q->whereHas('academicYear', function ($query) use ($academicYear) {
                                $query->where('year_label', $academicYear);
                            });
                        })
                        ->first();

                    if ($enrollment) {
                        $fee = \App\Models\AnnualSubscriptionFee::where('grade_id', $enrollment->grade_id)
                            ->where('academic_year_id', $enrollment->academic_year_id)
                            ->first();

                        if ($fee) {
                            $totalAmount += $fee->amount;
                        }
                    }
                }

                // احصل على المبلغ المدفوع من جدول الاقساط فقط (قسط سنوي فقط)
                $paidAmount = \App\Models\Installment::where('parent_id', $parent->id)
                    ->where('installment_type_id', 1) // قسط سنوي فقط
                    ->whereNotNull('payment_type_id')
                    ->when($academicYear, function ($q) use ($academicYear) {
                        $q->where('academic_year', $academicYear);
                    })
                    ->sum('amount');

                return [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'phone' => $parent->phone,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $totalAmount - $paidAmount,
                    'payment_percentage' => $totalAmount > 0 
                        ? ($paidAmount / $totalAmount) * 100 
                        : 0,
                ];
            })
            ->filter(fn ($record) => $record['total_amount'] > 0);

        $totalAmount = $records->sum('total_amount');
        $paidAmount = $records->sum('paid_amount');
        $remainingAmount = $records->sum('remaining_amount');

        try {
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('نظام تيجان');
            $pdf->SetAuthor('نظام تيجان');
            $pdf->SetTitle('تقرير الإيرادات');
            $pdf->SetSubject('تقرير الإيرادات');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->setRTL(true);

            // Title
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->SetTextColor(30, 58, 95);
            $pdf->Cell(0, 8, 'تقرير الإيرادات', 0, 1, 'C');
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, 'السنة الدراسية', 0, 1, 'C');
            $pdf->Ln(8);

            // بناء جدول HTML يأخذ العرض كامل
            $html = '<table border="1" cellpadding="3" cellspacing="0" style="width:100%; direction:rtl; text-align:right; font-size:9pt;">';
            $html .= '<tr style="background-color:#1e3a5f; color:white; font-weight:bold;">';
            $html .= '<th>ولي الأمر</th><th>رقم الجوال</th><th>المبلغ المستحق</th><th>المبلغ المدفوع</th><th>المبلغ المتبقي</th><th>نسبة التحصيل</th>';
            $html .= '</tr>';

            foreach ($records as $index => $record) {
                $bgColor = ($index % 2) ? '#f5f5f5' : '#ffffff';
                $html .= '<tr style="background-color:' . $bgColor . ';">';
                $html .= '<td>' . htmlspecialchars($record['name'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($record['phone'] ?? '') . '</td>';
                $html .= '<td>' . number_format($record['total_amount'], 2) . '</td>';
                $html .= '<td>' . number_format($record['paid_amount'], 2) . '</td>';
                $html .= '<td>' . number_format($record['remaining_amount'], 2) . '</td>';
                $html .= '<td>' . number_format($record['payment_percentage'], 1) . '%</td>';
                $html .= '</tr>';
            }

            // Totals row
            $html .= '<tr style="background-color:#2c5282; color:white; font-weight:bold;">';
            $html .= '<td colspan="2">الإجمالي</td>';
            $html .= '<td>' . number_format($totalAmount, 2) . '</td>';
            $html .= '<td>' . number_format($paidAmount, 2) . '</td>';
            $html .= '<td>' . number_format($remainingAmount, 2) . '</td>';
            $html .= '<td>' . number_format(($totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0), 1) . '%</td>';
            $html .= '</tr>';
            
            $html .= '</table>';

            // طباعة الجدول باستخدام HTML
            $pdf->writeHTML($html, true, false, true, false, '');

            $filename = 'تقرير_الإيرادات_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $content = $pdf->Output($filename, 'S');

            return response()->streamDownload(
                fn () => print($content),
                $filename,
                ['Content-Type' => 'application/pdf'],
            );
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في توليد PDF: ' . $e->getMessage());
        }
    }

    public function downloadAttendanceOverallPdf()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission') || !$user->hasPermission('attendance-report.view')) {
            abort(403, 'غير مصرح بالوصول');
        }

        $startDate = request('startDate');
        $endDate = request('endDate');

        // الحصول على البيانات
        $employees = \App\Models\Employee::query()
            ->whereHas('teacherClasses')
            ->get();

        $records = $employees->map(function ($employee) use ($startDate, $endDate) {
            $attendanceRecords = \App\Models\DailyClassAttendance::query()
                ->where('employee_id', $employee->id)
                ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
                ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
                ->get();

            $totalSessions = $attendanceRecords->count();
            $attendedSessions = $attendanceRecords->where('status', 'completed')->count();
            $absentSessions = $totalSessions - $attendedSessions;
            $percentage = $totalSessions > 0 ? ($attendedSessions / $totalSessions) * 100 : 0;

            return [
                'name' => $employee->name,
                'total' => $totalSessions,
                'attended' => $attendedSessions,
                'absent' => $absentSessions,
                'percentage' => number_format($percentage, 1),
            ];
        })->filter(fn ($r) => $r['total'] > 0)->values();

        $totalSessions = $records->sum('total');
        $totalAttended = $records->sum('attended');
        $totalAbsent = $records->sum('absent');

        try {
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('نظام تيجان');
            $pdf->SetAuthor('نظام تيجان');
            $pdf->SetTitle('تقرير الحضور الإجمالي');
            $pdf->SetSubject('تقرير الحضور الإجمالي');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->setRTL(true);

            // Title
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->SetTextColor(30, 58, 95);
            $pdf->Cell(0, 8, 'تقرير الحضور الإجمالي', 0, 1, 'C');
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, 'من: ' . ($startDate ?? 'البداية') . ' إلى: ' . ($endDate ?? 'النهاية'), 0, 1, 'C');
            $pdf->Ln(8);

            // Table Header
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->SetFillColor(30, 58, 95);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(70, 7, 'اسم المعلم/ة', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'إجمالي', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'حاضر', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'غايب', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'النسبة %', 1, 1, 'C', true);

            // Table Data
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            foreach ($records as $index => $record) {
                $bgColor = ($index % 2) ? [245, 245, 245] : [255, 255, 255];
                $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
                $pdf->Cell(70, 6, $record['name'], 1, 0, 'R', true);
                $pdf->Cell(30, 6, $record['total'], 1, 0, 'C', true);
                $pdf->Cell(30, 6, $record['attended'], 1, 0, 'C', true);
                $pdf->Cell(30, 6, $record['absent'], 1, 0, 'C', true);
                $pdf->Cell(30, 6, $record['percentage'] . '%', 1, 1, 'C', true);
            }

            // Total
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->SetFillColor(44, 82, 130);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(70, 7, 'الإجمالي', 1, 0, 'C', true);
            $pdf->Cell(30, 7, $totalSessions, 1, 0, 'C', true);
            $pdf->Cell(30, 7, $totalAttended, 1, 0, 'C', true);
            $pdf->Cell(30, 7, $totalAbsent, 1, 0, 'C', true);
            $pdf->Cell(30, 7, number_format(($totalSessions > 0 ? ($totalAttended / $totalSessions) * 100 : 0), 1) . '%', 1, 1, 'C', true);

            $filename = 'تقرير_الحضور_الإجمالي_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $content = $pdf->Output($filename, 'S');

            return response()->streamDownload(
                fn () => print($content),
                $filename,
                ['Content-Type' => 'application/pdf'],
            );
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في توليد PDF: ' . $e->getMessage());
        }
    }

    public function downloadAttendanceDetailedPdf()
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'hasPermission') || !$user->hasPermission('attendance-report.view')) {
            abort(403, 'غير مصرح بالوصول');
        }

        $employeeId = request('employeeId');
        $startDate = request('startDate');
        $endDate = request('endDate');

        if (!$employeeId) {
            return back()->with('error', 'يجب اختيار معلم/ة');
        }

        $employee = \App\Models\Employee::find($employeeId);
        if (!$employee) {
            return back()->with('error', 'المعلم/ة غير موجود');
        }

        $records = \App\Models\DailyClassAttendance::query()
            ->where('employee_id', $employeeId)
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', $endDate))
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        $totalSessions = $records->count();
        $attendedSessions = $records->where('status', 'completed')->count();
        $absentSessions = $totalSessions - $attendedSessions;

        try {
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('نظام تيجان');
            $pdf->SetAuthor('نظام تيجان');
            $pdf->SetTitle('تقرير الحضور التفصيلي');
            $pdf->SetSubject('تقرير الحضور التفصيلي');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->setRTL(true);

            // Title
            $pdf->SetFont('dejavusans', 'B', 16);
            $pdf->SetTextColor(30, 58, 95);
            $pdf->Cell(0, 8, 'تقرير الحضور التفصيلي', 0, 1, 'C');
            
            // Employee Info
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->Cell(0, 6, 'المعلم/ة: ' . $employee->name, 0, 1, 'R');
            
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, 'من: ' . ($startDate ?? 'البداية') . ' إلى: ' . ($endDate ?? 'النهاية'), 0, 1, 'R');
            
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->Cell(0, 4, 'إجمالي: ' . $totalSessions . ' | حاضر: ' . $attendedSessions . ' | غايب: ' . $absentSessions, 0, 1, 'R');
            $pdf->Ln(6);

            // Table Header
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->SetFillColor(30, 58, 95);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(30, 6, 'التاريخ', 1, 0, 'C', true);
            $pdf->Cell(20, 6, 'البداية', 1, 0, 'C', true);
            $pdf->Cell(20, 6, 'النهاية', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'الحالة', 1, 0, 'C', true);
            $pdf->Cell(25, 6, 'الدخول', 1, 0, 'C', true);
            $pdf->Cell(25, 6, 'الخروج', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'المدة', 1, 1, 'C', true);

            // Table Data
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->SetTextColor(0, 0, 0);
            foreach ($records as $index => $record) {
                $bgColor = ($index % 2) ? [245, 245, 245] : [255, 255, 255];
                $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);

                $status = match ($record->status) {
                    'completed' => 'حاضر',
                    'checked_in' => 'جزئي',
                    'pending' => 'غايب',
                    default => $record->status,
                };

                $duration = '-';
                if ($record->check_in_at && $record->check_out_at) {
                    $start = \Carbon\Carbon::parse($record->check_in_at);
                    $end = \Carbon\Carbon::parse($record->check_out_at);
                    $minutes = $end->diffInMinutes($start);
                    $duration = floor($minutes / 60) . 'h' . ($minutes % 60) . 'm';
                }

                $pdf->Cell(30, 5, $record->date, 1, 0, 'C', true);
                $pdf->Cell(20, 5, \Carbon\Carbon::parse($record->start_time)->format('H:i'), 1, 0, 'C', true);
                $pdf->Cell(20, 5, \Carbon\Carbon::parse($record->end_time)->format('H:i'), 1, 0, 'C', true);
                $pdf->Cell(30, 5, $status, 1, 0, 'C', true);
                $pdf->Cell(25, 5, $record->check_in_at ? \Carbon\Carbon::parse($record->check_in_at)->format('H:i') : '-', 1, 0, 'C', true);
                $pdf->Cell(25, 5, $record->check_out_at ? \Carbon\Carbon::parse($record->check_out_at)->format('H:i') : '-', 1, 0, 'C', true);
                $pdf->Cell(30, 5, $duration, 1, 1, 'C', true);
            }

            $filename = 'تقرير_حضور_' . str_replace(' ', '_', $employee->name) . '_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            $content = $pdf->Output($filename, 'S');

            return response()->streamDownload(
                fn () => print($content),
                $filename,
                ['Content-Type' => 'application/pdf'],
            );
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في توليد PDF: ' . $e->getMessage());
        }
    }
}
