<?php

namespace App\Filament\Pages\Reports;

use App\Models\academic_years;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Salary;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class SalariesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'تقرير الرواتب';
    protected static string|\UnitEnum|null $navigationGroup = 'تقارير';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.pages.reports.salaries-report';
    protected static ?string $title = 'تقرير صرف الرواتب';

    public ?string $selectedMonth = null;
    public ?string $selectedYear = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?int $selectedEmployeeType = null;

    public function mount(): void
    {
        // لا نعيّن افتراضيات - نعرض جميع البيانات بشكل افتراضي
        
        // تسجيل الحدث
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'viewed',
                'description' => 'عرض تقرير الرواتب',
                'model_type' => Salary::class,
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

        return $user->hasPermission('salaries-report.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredTableQuery())
            ->columns([
                TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('month')
                    ->label('الشهر')
                    ->formatStateUsing(function ($state) {
                        $months = [
                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                        ];
                        return $months[$state] ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('year')
                    ->label('السنة')
                    ->sortable(),

                TextColumn::make('basic_salary')
                    ->label('الراتب الأساسي')
                    ->money('LYD', locale: 'en')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->money('LYD', locale: 'en')
                    ]),

                TextColumn::make('bonus_amount')
                    ->label('الحوافز')
                    ->money('LYD', locale: 'en')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->money('LYD', locale: 'en')
                    ]),

                TextColumn::make('deduction_amount')
                    ->label('الخصومات')
                    ->money('LYD', locale: 'en')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->money('LYD', locale: 'en')
                    ]),

                TextColumn::make('net_salary')
                    ->label('الصافي')
                    ->money('LYD', locale: 'en')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('الإجمالي')
                            ->money('LYD', locale: 'en')
                    ]),

                TextColumn::make('sessions_count')
                    ->label('عدد الحصص')
                    ->numeric(),

                TextColumn::make('attendance_days')
                    ->label('أيام الحضور')
                    ->numeric(),

                TextColumn::make('payment_date')
                    ->label('تاريخ الصرف')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('paymentMethod.payment_type')
                    ->label('طريقة الدفع')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('الشهر')
                    ->attribute('month')
                    ->options([
                        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                    ]),

                SelectFilter::make('year')
                    ->label('السنة')
                    ->attribute('year')
                    ->options(
                        Salary::distinct()
                            ->pluck('year')
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->sortDesc()
                    ),

                SelectFilter::make('emp_id')
                    ->label('الموظف')
                    ->options(
                        Employee::orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable(),

                SelectFilter::make('selectedEmployeeType')
                    ->label('نوع الموظف')
                    ->attribute('employee.emp_type_id')
                    ->options(
                        \App\Models\EmployeeType::orderBy('type_name')
                            ->pluck('type_name', 'id')
                    )
                    ->searchable(),

                Filter::make('payment_date_range')
                    ->label('نطاق التاريخ')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('start_date')
                            ->label('من التاريخ'),
                        \Filament\Forms\Components\DatePicker::make('end_date')
                            ->label('إلى التاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['start_date'] ?? null,
                                fn ($query, $date) => $query->whereDate('payment_date', '>=', $date)
                            )
                            ->when(
                                $data['end_date'] ?? null,
                                fn ($query, $date) => $query->whereDate('payment_date', '<=', $date)
                            );
                    }),
            ])
            ->defaultSort('payment_date', 'desc')
            ->striped();
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
        return redirect()->route('reports.salaries.pdf', [
            'month' => $this->selectedMonth,
            'year' => $this->selectedYear,
            'employeeType' => $this->selectedEmployeeType,
        ]);
    }

    public function getFilteredTableQuery(): ?Builder
    {
        return Salary::query()
            ->with(['employee', 'employee.employeeType', 'paymentMethod'])
            ->when($this->selectedMonth, fn ($query) => $query->where('month', $this->selectedMonth))
            ->when($this->selectedYear, fn ($query) => $query->where('year', $this->selectedYear))
            ->when($this->selectedEmployeeType, fn ($query) => $query->whereHas('employee', function ($q) {
                $q->where('emp_type_id', $this->selectedEmployeeType);
            }))
            ->orderBy('payment_date', 'desc');
    }
}
