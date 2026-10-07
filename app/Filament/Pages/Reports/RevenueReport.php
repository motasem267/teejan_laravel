<?php

namespace App\Filament\Pages\Reports;

use App\Models\academic_years;
use App\Models\ActivityLog;
use App\Models\Installment;
use App\Models\ParentModel;
use App\Services\RevenueCalculator;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RevenueReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'تقرير الإيرادات';
    protected static string|\UnitEnum|null $navigationGroup = 'تقارير';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 12;
    protected string $view = 'filament.pages.reports.revenue-report';
    protected static ?string $title = 'تقرير الأقساط والإيرادات';

    private ?RevenueCalculator $calculator = null;

    public function mount(): void
    {
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
        $amount = fn (string $key) => fn (ParentModel $record) => $this->totals($record)[$key];
        $money = fn ($state) => 'د.ل ' . number_format($state ?? 0, 2);

        return $table
            ->query(ParentModel::query())
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
                    ->alignment('center')
                    ->getStateUsing($amount('due'))
                    ->formatStateUsing($money),

                TextColumn::make('total_paid')
                    ->label('المبلغ المدفوع')
                    ->alignment('center')
                    ->getStateUsing($amount('paid'))
                    ->formatStateUsing($money),

                TextColumn::make('remaining')
                    ->label('المبلغ المتبقي')
                    ->alignment('center')
                    ->getStateUsing($amount('remaining'))
                    ->formatStateUsing($money),

                TextColumn::make('payment_percentage')
                    ->label('نسبة التحصيل')
                    ->alignment('center')
                    ->getStateUsing($amount('percentage'))
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 1) . '%'),
            ])
            ->filters([
                // أولياء الأمور الذين لديهم أبناء مقيدين في السنة المختارة (الافتراضي: السنة الفعالة)
                SelectFilter::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->options(fn () => academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                    ->default(academic_years::getActiveId())
                    ->query(fn (Builder $query, array $data) => $query->whereHas(
                        'students.enrollments',
                        fn ($q) => $q->when($data['value'] ?? null, fn ($q, $yearId) => $q->where('academic_year_id', $yearId)),
                    )),

                SelectFilter::make('paymentStatus')
                    ->label('حالة الدفع')
                    ->options([
                        'completed' => 'مكتمل',
                        'incomplete' => 'غير مكتمل',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $status = $data['value'] ?? null;
                        if (!$status) {
                            return $query;
                        }

                        $yearId = $this->selectedYearId();
                        $withYear = $yearId !== null;
                        $due = RevenueCalculator::dueSql($withYear, 'parents.id');
                        $paid = RevenueCalculator::paidSql($withYear, 'parents.id');
                        $bind = $withYear ? [$yearId] : [];

                        return $query
                            ->whereRaw("$due > 0", $bind)
                            ->whereRaw($status === 'completed' ? "$paid >= $due" : "$paid < $due", [...$bind, ...$bind]);
                    }),
            ])
            ->defaultSort('name')
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
        return redirect()->route('reports.revenue.pdf', array_filter([
            'year_id' => $this->selectedYearId(),
        ]));
    }

    private function selectedYearId(): ?int
    {
        $value = $this->tableFilters['academic_year_id']['value'] ?? null;

        return filled($value) ? (int) $value : null;
    }

    private function totals(ParentModel $record): array
    {
        $this->calculator ??= new RevenueCalculator;

        return $this->calculator->forParent((int) $record->id, $this->selectedYearId());
    }
}
