<?php

namespace App\Filament\Pages\Reports;

use App\Models\academic_years;
use App\Models\ActivityLog;
use App\Models\Installment;
use App\Models\ParentModel;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class RevenueReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'تقرير الإيرادات';
    protected static string|\UnitEnum|null $navigationGroup = 'تقارير';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 12;
    protected string $view = 'filament.pages.reports.revenue-report';
    protected static ?string $title = 'تقرير الأقساط والإيرادات';

    public ?string $selectedYear = null;
    public ?string $paymentStatus = null;

    public function mount(): void
    {
        // عدم تعيين السنة الدراسية بشكل افتراضي - دع المستخدم يختار
        // $this->selectedYear = academic_years::where('is_active', true)->first()?->year_label;
        
        // تسجيل الحدث
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'viewed',
                'description' => 'عرض تقرير الإيرادات',
                'model_type' => Installment::class,
            ]);
        } catch (\Exception $e) {
            // تجاهل أخطاء التسجيل
        }
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        return $user->hasPermission('revenue-report.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('ولي الأمر')
                    ->sortable()
                    ->searchable()
                    ->alignment('center'),

                TextColumn::make('phone')
                    ->label('رقم الجوال')
                    ->sortable()
                    ->alignment('center'),

                TextColumn::make('total_due')
                    ->label('المبلغ المستحق')
                    ->sortable()
                    ->alignment('center')
                    ->getStateUsing(function ($record) {
                        // احصل على جميع أطفال ولي الأمر
                        $students = $record->students;
                        $totalDue = 0;

                        foreach ($students as $student) {
                            // احصل على التسجيل الأكاديمي للطالب
                            $enrollment = $student->enrollments()
                                ->when($this->selectedYear, function ($q) {
                                    $q->whereHas('academicYear', function ($query) {
                                        $query->where('year_label', $this->selectedYear);
                                    });
                                })
                                ->first();

                            if ($enrollment) {
                                // احصل على رسم الاشتراك السنوي للصف والسنة
                                $fee = \App\Models\AnnualSubscriptionFee::where('grade_id', $enrollment->grade_id)
                                    ->where('academic_year_id', $enrollment->academic_year_id)
                                    ->first();

                                if ($fee) {
                                    $totalDue += $fee->amount;
                                }
                            }
                        }

                        return $totalDue;
                    })
                    ->formatStateUsing(fn ($state) => 'د.ل ' . number_format($state ?? 0, 2)),

                TextColumn::make('total_paid')
                    ->label('المبلغ المدفوع')
                    ->sortable()
                    ->alignment('center')
                    ->getStateUsing(function ($record) {
                        // احصل على المبلغ المدفوع من جدول الاقساط فقط (قسط سنوي فقط)
                        $paid = \App\Models\Installment::where('parent_id', $record->id)
                            ->where('installment_type_id', 1) // قسط سنوي فقط
                            ->whereNotNull('payment_type_id')
                            ->when($this->selectedYear, function ($q) {
                                $q->where('academic_year', $this->selectedYear);
                            })
                            ->sum('amount');

                        return $paid;
                    })
                    ->formatStateUsing(fn ($state) => 'د.ل ' . number_format($state ?? 0, 2)),

                TextColumn::make('remaining')
                    ->label('المبلغ المتبقي')
                    ->sortable()
                    ->alignment('center')
                    ->getStateUsing(function ($record) {
                        // احصل على المستحق
                        $students = $record->students;
                        $totalDue = 0;

                        foreach ($students as $student) {
                            $enrollment = $student->enrollments()
                                ->when($this->selectedYear, function ($q) {
                                    $q->whereHas('academicYear', function ($query) {
                                        $query->where('year_label', $this->selectedYear);
                                    });
                                })
                                ->first();

                            if ($enrollment) {
                                $fee = \App\Models\AnnualSubscriptionFee::where('grade_id', $enrollment->grade_id)
                                    ->where('academic_year_id', $enrollment->academic_year_id)
                                    ->first();

                                if ($fee) {
                                    $totalDue += $fee->amount;
                                }
                            }
                        }

                        // احصل على المدفوع
                        $totalPaid = \App\Models\Installment::where('parent_id', $record->id)
                            ->where('installment_type_id', 1) // قسط سنوي فقط
                            ->whereNotNull('payment_type_id')
                            ->when($this->selectedYear, function ($q) {
                                $q->where('academic_year', $this->selectedYear);
                            })
                            ->sum('amount');

                        // احسب الفرق
                        return $totalDue - $totalPaid;
                    })
                    ->formatStateUsing(fn ($state) => 'د.ل ' . number_format($state ?? 0, 2)),

                TextColumn::make('payment_percentage')
                    ->label('نسبة التحصيل')
                    ->sortable()
                    ->alignment('center')
                    ->getStateUsing(function ($record) {
                        // احصل على المستحق
                        $students = $record->students;
                        $totalDue = 0;

                        foreach ($students as $student) {
                            $enrollment = $student->enrollments()
                                ->when($this->selectedYear, function ($q) {
                                    $q->whereHas('academicYear', function ($query) {
                                        $query->where('year_label', $this->selectedYear);
                                    });
                                })
                                ->first();

                            if ($enrollment) {
                                $fee = \App\Models\AnnualSubscriptionFee::where('grade_id', $enrollment->grade_id)
                                    ->where('academic_year_id', $enrollment->academic_year_id)
                                    ->first();

                                if ($fee) {
                                    $totalDue += $fee->amount;
                                }
                            }
                        }

                        // احصل على المدفوع
                        $totalPaid = \App\Models\Installment::where('parent_id', $record->id)
                            ->where('installment_type_id', 1) // قسط سنوي فقط
                            ->whereNotNull('payment_type_id')
                            ->when($this->selectedYear, function ($q) {
                                $q->where('academic_year', $this->selectedYear);
                            })
                            ->sum('amount');

                        // احسب النسبة
                        return $totalDue > 0 ? ($totalPaid / $totalDue) * 100 : 0;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 1) . '%'),
            ])
            ->filters([
                SelectFilter::make('selectedYear')
                    ->label('السنة الدراسية')
                    ->attribute('academic_year')
                    ->options(
                        academic_years::orderByDesc('id')
                            ->pluck('year_label', 'year_label')
                    ),

                SelectFilter::make('paymentStatus')
                    ->label('حالة الدفع')
                    ->options([
                        'completed' => 'مكتمل',
                        'incomplete' => 'غير مكتمل',
                    ]),
            ])
            ->defaultSort('name')
            ->striped();
    }

    protected function getRevenueQuery()
    {
        $query = ParentModel::query()
            ->select([
                'parents.id',
                'parents.name as parent_name',
                'parents.phone as parent_phone',
                'installments.academic_year',
                DB::raw('SUM(installments.amount) as total_amount'),
                DB::raw('SUM(CASE WHEN installments.payment_type_id IS NOT NULL THEN installments.amount ELSE 0 END) as paid_amount'),
            ])
            ->leftJoin('installments', 'parents.id', '=', 'installments.parent_id')
            ->groupBy('parents.id', 'parents.name', 'parents.phone', 'installments.academic_year');

        // حساب المتبقي والنسبة المئوية في PHP
        return $query->get()->map(function ($parent) {
            $totalAmount = $parent->total_amount ?? 0;
            $paidAmount = $parent->paid_amount ?? 0;
            
            return [
                'parent_name' => $parent->parent_name,
                'parent_phone' => $parent->parent_phone,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $totalAmount - $paidAmount,
                'payment_percentage' => $totalAmount > 0 ? ($paidAmount / $totalAmount * 100) : 0,
                'academic_year' => $parent->academic_year ?? 'غير محدد',
            ];
        });
    }

    public function getTableQueryString(): string
    {
        return '';
    }

    public function getFilteredTableQuery(): ?Builder
    {
        $query = ParentModel::query();

        // فلتر السنة الدراسية - عرض الأولياء الذين لديهم طلاب مسجلين في تلك السنة
        if ($this->selectedYear) {
            $query->whereHas('students.enrollments.academicYear', function ($q) {
                $q->where('year_label', $this->selectedYear);
            });
        } else {
            // عرض الأولياء الذين لديهم طلاب مسجلين
            $query->whereHas('students.enrollments');
        }

        // تطبيق فلتر حالة الدفع فقط إذا تم اختياره
        if ($this->paymentStatus === 'completed') {
            // المكتمل: المدفوع == المستحق
            $query->where(function ($q) {
                $q->whereRaw('(
                    SELECT COALESCE(SUM(asf.amount), 0)
                    FROM annual_subscription_fees asf
                    JOIN student_enrollments se ON se.grade_id = asf.grade_id AND se.academic_year_id = asf.academic_year_id
                    JOIN students s ON s.id = se.student_id
                    WHERE s.parent_id = parents.id
                    ' . ($this->selectedYear ? ' AND ay.year_label = "' . $this->selectedYear . '"' : '') . '
                ) = (
                    SELECT COALESCE(SUM(i.amount), 0)
                    FROM installments i
                    WHERE i.parent_id = parents.id
                    AND i.installment_type_id = 1
                    AND i.payment_type_id IS NOT NULL
                    ' . ($this->selectedYear ? ' AND i.academic_year = "' . $this->selectedYear . '"' : '') . '
                )
                AND (
                    SELECT COALESCE(SUM(asf.amount), 0)
                    FROM annual_subscription_fees asf
                    JOIN student_enrollments se ON se.grade_id = asf.grade_id AND se.academic_year_id = asf.academic_year_id
                    JOIN students s ON s.id = se.student_id
                    WHERE s.parent_id = parents.id
                    ' . ($this->selectedYear ? ' AND ay.year_label = "' . $this->selectedYear . '"' : '') . '
                ) > 0');
            });
        } elseif ($this->paymentStatus === 'incomplete') {
            // غير المكتمل: المدفوع < المستحق
            $query->where(function ($q) {
                $q->whereRaw('(
                    SELECT COALESCE(SUM(i.amount), 0)
                    FROM installments i
                    WHERE i.parent_id = parents.id
                    AND i.installment_type_id = 1
                    AND i.payment_type_id IS NOT NULL
                    ' . ($this->selectedYear ? ' AND i.academic_year = "' . $this->selectedYear . '"' : '') . '
                ) < (
                    SELECT COALESCE(SUM(asf.amount), 0)
                    FROM annual_subscription_fees asf
                    JOIN student_enrollments se ON se.grade_id = asf.grade_id AND se.academic_year_id = asf.academic_year_id
                    JOIN students s ON s.id = se.student_id
                    WHERE s.parent_id = parents.id
                    ' . ($this->selectedYear ? ' AND ay.year_label = "' . $this->selectedYear . '"' : '') . '
                )
                AND (
                    SELECT COALESCE(SUM(asf.amount), 0)
                    FROM annual_subscription_fees asf
                    JOIN student_enrollments se ON se.grade_id = asf.grade_id AND se.academic_year_id = asf.academic_year_id
                    JOIN students s ON s.id = se.student_id
                    WHERE s.parent_id = parents.id
                    ' . ($this->selectedYear ? ' AND ay.year_label = "' . $this->selectedYear . '"' : '') . '
                ) > 0');
            });
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('تحميل PDF')
                ->icon('heroicon-o-document')
                ->action('downloadPdf'),
        ];
    }

    public function downloadPdf()
    {
        return redirect()->route('reports.revenue.pdf', [
            'year' => $this->selectedYear,
        ]);
    }
}
