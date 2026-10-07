<?php

namespace App\Filament\Resources\EmployeeEnrollments\Tables;

use App\Models\academic_years;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeEnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_id')
                    ->label('رقم الموظف')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('employee.name')
                    ->label('الموظف')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('employeeType.type_name')
                    ->label('الوظيفة')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('academicYear.year_label')
                    ->label('السنة الدراسية')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ القيد')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->relationship('academicYear', 'year_label')
                    ->default(academic_years::getActiveId())
                    ->preload(),

                SelectFilter::make('emp_type_id')
                    ->label('الوظيفة')
                    ->relationship('employeeType', 'type_name')
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('employee.name');
    }
}
