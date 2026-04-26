<?php

namespace App\Filament\Pages\Reports;

use App\Models\DailyClassAttendance;
use App\Models\Employee;
use App\Models\ActivityLog;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class AttendanceDetailedReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'تقرير الحضور التفصيلي';
    protected static string|\UnitEnum|null $navigationGroup = 'تقارير';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 21;
    protected string $view = 'filament.pages.reports.attendance-detailed-report';
    protected static ?string $title = 'تقرير حضور المعلمين بالتفصيل';

    public ?int $selectedEmployeeId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        // تحديد التاريخ الافتراضي
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // تسجيل الحدث
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'viewed',
                'description' => 'عرض تقرير الحضور التفصيلي',
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
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('وقت البداية')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i'))
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('وقت النهاية')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('H:i'))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'completed' => 'حاضر ✓',
                            'checked_in' => 'حاضر جزئي',
                            'pending' => 'غايب ✗',
                            default => $state,
                        };
                    })
                    ->color(function ($state) {
                        return match ($state) {
                            'completed' => 'success',
                            'checked_in' => 'warning',
                            'pending' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->sortable(),

                TextColumn::make('check_in_at')
                    ->label('وقت الدخول')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-')
                    ->sortable(),

                TextColumn::make('check_out_at')
                    ->label('وقت الخروج')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('H:i') : '-')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('employee_filter')
                    ->label('اختر المعلم/ة')
                    ->form([
                        Select::make('employee_id')
                            ->label('المعلم/ة')
                            ->options(function () {
                                return Employee::query()
                                    ->whereHas('teacherClasses')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        $this->selectedEmployeeId = $data['employee_id'] ?? null;
                        return $query;
                    }),

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
            ->defaultSort('date', 'desc')
            ->striped();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('تحميل PDF')
                ->icon('heroicon-o-document')
                ->action('downloadPdf')
                ->disabled(fn () => !$this->selectedEmployeeId),
        ];
    }

    public function downloadPdf()
    {
        if (!$this->selectedEmployeeId) {
            return;
        }

        return redirect()->route('reports.attendance-detailed.pdf', [
            'employeeId' => $this->selectedEmployeeId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function getFilteredTableQuery(): Builder
    {
        $query = DailyClassAttendance::query();

        if ($this->selectedEmployeeId) {
            $query->where('employee_id', $this->selectedEmployeeId);
        }

        return $query
            ->when($this->startDate, fn ($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('date', '<=', $this->endDate))
            ->with('employee')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc');
    }
}
