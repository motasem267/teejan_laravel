<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Actions\IdCardActions;
use App\Models\academic_years;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('رقم الموظف')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employeeType.type_name')
                    ->label('نوع الموظف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.status_name')
                    ->label('الحالة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->label('رقم الهاتف')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('academic_year')
                    ->label('مقيد في السنة الدراسية')
                    ->options(fn () => academic_years::orderByDesc('id')->pluck('year_label', 'id'))
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($q, $yearId) => $q->whereHas('enrollments', fn ($e) => $e->where('academic_year_id', $yearId)),
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                IdCardActions::employeeRecord(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    IdCardActions::employeesBulk(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
