<?php

namespace App\Filament\Pages\Reports;

use App\Models\academic_years;
use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ExpensesType;
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

class ExpensesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'تقرير المصاريف';
    protected static string|\UnitEnum|null $navigationGroup = 'تقارير';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 10;
    protected string $view = 'filament.pages.reports.expenses-report';
    protected static ?string $title = 'تقرير إجمالي المصاريف';

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        // تعيين التاريخ الافتراضي - من بداية السنة إلى الآن
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // تسجيل الحدث
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'viewed',
                'description' => 'عرض تقرير المصاريف',
                'model_type' => Expense::class,
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

        return $user->hasPermission('expenses-report.view');
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

                TextColumn::make('expensesType.expenses_type_name')
                    ->label('نوع المصروف')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('LYD', locale: 'en')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('الإجمالي')
                            ->money('LYD', locale: 'en')
                    ]),

                TextColumn::make('paymentMethod.payment_type')
                    ->label('طريقة الدفع')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('المسجل بواسطة')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
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

                SelectFilter::make('expenses_type_id')
                    ->label('نوع المصروف')
                    ->options(
                        ExpensesType::orderBy('expenses_type_name')
                            ->pluck('expenses_type_name', 'id')
                    )
                    ->searchable(),

                SelectFilter::make('payment_method_id')
                    ->label('طريقة الدفع')
                    ->options(
                        \App\Models\PaymentMethod::orderBy('payment_type')
                            ->pluck('payment_type', 'id')
                    ),
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
                ->action('downloadPdf'),
        ];
    }

    public function downloadPdf()
    {
        return redirect()->route('reports.expenses.pdf', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function getFilteredTableQuery(): ?Builder
    {
        return Expense::query()
            ->when($this->startDate, fn ($query) => $query->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($query) => $query->whereDate('date', '<=', $this->endDate));
    }
}
