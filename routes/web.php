<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect('/admin');
});

// Report PDF Download Routes
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/reports/expenses-pdf', [ReportController::class, 'downloadExpensesPdf'])->name('reports.expenses.pdf');
    Route::get('/reports/salaries-pdf', [ReportController::class, 'downloadSalariesPdf'])->name('reports.salaries.pdf');
    Route::get('/reports/revenue-pdf', [ReportController::class, 'downloadRevenuePdf'])->name('reports.revenue.pdf');
    Route::get('/reports/attendance-overall-pdf', [ReportController::class, 'downloadAttendanceOverallPdf'])->name('reports.attendance-overall.pdf');
    Route::get('/reports/attendance-detailed-pdf', [ReportController::class, 'downloadAttendanceDetailedPdf'])->name('reports.attendance-detailed.pdf');
});
