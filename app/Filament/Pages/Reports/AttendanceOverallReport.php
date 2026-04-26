<?php

namespace App\Filament\Pages\Reports;

use App\Models\DailyClassAttendance;
use App\Models\Employee;
use App\Models\ActivityLog;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class AttendanceOverallReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'تقرير الحضور الإجمالي';
    protected static string|\UnitEnum|null $navigationGroup = 'تقارير';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 20;
    protected string $view = 'filament.pages.reports.attendance-overall-report';
    protected static ?string $title = 'تقرير الحضور والغياب الإجمالي';

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        // تحديد التاريخ الافتراضي - شهر حالي
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // تسجيل الحدث
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'viewed',
                'description' => 'عرض تقرير الحضور الإجمالي',
                'model_type' => DailyClassAttendance::class,
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

        return $user->hasPermission('attendance-report.view') || 
               $user->hasPermission('school-schedules.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredTableQuery())
            ->columns([
                TextColumn::make('employee_name')
                    ->label('اسم المعلم/ة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_sessions')
                    ->label('إجمالي الحصص')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                    ]),

                TextColumn::make('attended_sessions')
                    ->label('حصص حاضر')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "✓ {$state}")
                    ->color('success')
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                    ]),

                TextColumn::make('absent_sessions')
                    ->label('حصص غايب')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "✗ {$state}")
                    ->color('danger')
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                    ]),

                TextColumn::make('local_sessions')
                    ->label('حصص محلية')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('international_sessions')
                    ->label('حصص دولية')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('attendance_percentage')
                    ->label('نسبة الحضور %')
                    ->numeric()
                    ->sortable()
                    ->color(function ($state) {
                        $percentage = (float) str_replace('%', '', $state);
                        if ($percentage >= 85) return 'success';
                        if ($percentage >= 70) return 'warning';
                        return 'danger';
                    }),
            ])
            ->filters([
                Filter::make('date_range')
                    ->label('نطاق التاريخ')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('start_date')
                            ->label('من التاريخ'),
                        \Filament\Forms\Components\DatePicker::make('end_date')
                            ->label('إلى التاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        $this->startDate = $data['start_date'] ?? null;
                        $this->endDate = $data['end_date'] ?? null;
                        return $query
                            ->when(
                                $data['start_date'] ?? null,
                                fn ($query, $date) => $query->whereDate('date', '>=', $date)
                            )
                            ->when(
                                $data['end_date'] ?? null,
                                fn ($query, $date) => $query->whereDate('date', '<=', $date)
                            );
                    }),
            ])
            ->defaultSort('employee_name')
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
        return redirect()->route('reports.attendance-overall.pdf', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function getFilteredTableQuery(): Builder
    {
        return Employee::query()
            ->whereHas('teacherClasses')
            ->leftJoin('daily_class_attendance', 'employees.id', '=', 'daily_class_attendance.employee_id')
            ->when($this->startDate, fn ($q) => $q->whereDate('daily_class_attendance.date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('daily_class_attendance.date', '<=', $this->endDate))
            ->groupBy('employees.id', 'employees.name')
            ->selectRaw(
                'employees.id,
                employees.name as employee_name,
                COUNT(daily_class_attendance.id) as total_sessions,
                SUM(CASE WHEN daily_class_attendance.status = "completed" THEN 1 ELSE 0 END) as attended_sessions,
                SUM(CASE WHEN daily_class_attendance.status = "completed" THEN 0 ELSE 1 END) as absent_sessions,
                COUNT(daily_class_attendance.id) as local_sessions,
                0 as international_sessions,
                CONCAT(ROUND(SUM(CASE WHEN daily_class_attendance.status = "completed" THEN 1 ELSE 0 END) / COUNT(daily_class_attendance.id) * 100, 1), "%") as attendance_percentage'
            )
            ->having('total_sessions', '>', 0)
            ->orderBy('employees.name');
    }
}
