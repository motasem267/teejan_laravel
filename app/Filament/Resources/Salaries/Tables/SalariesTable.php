<?php

namespace App\Filament\Resources\Salaries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('اسم الموظف')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('month')
                    ->label('الشهر')
                    ->formatStateUsing(fn ($state) => match($state) {
                        1 => 'يناير',
                        2 => 'فبراير',
                        3 => 'مارس',
                        4 => 'أبريل',
                        5 => 'مايو',
                        6 => 'يونيو',
                        7 => 'يوليو',
                        8 => 'أغسطس',
                        9 => 'سبتمبر',
                        10 => 'أكتوبر',
                        11 => 'نوفمبر',
                        12 => 'ديسمبر',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('year')
                    ->label('السنة')
                    ->sortable(),

                TextColumn::make('basic_salary')
                    ->label('الراتب الأساسي')
                    ->money('LYD')
                    ->sortable(),

                TextColumn::make('bonus_amount')
                    ->label('الحوافز')
                    ->money('LYD')
                    ->sortable(),

                TextColumn::make('deduction_amount')
                    ->label('الخصومات')
                    ->money('LYD')
                    ->sortable(),

                TextColumn::make('sessions_count')
                    ->label('عدد الحصص')
                    ->sortable()
                    ->toggleable()
                    ->default('-'),

                TextColumn::make('attendance_days')
                    ->label('أيام الحضور')
                    ->sortable()
                    ->toggleable()
                    ->default('-'),

                TextColumn::make('net_salary')
                    ->label('صافي الراتب')
                    ->money('LYD')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date('Y-m-d')
                    ->sortable()
                    ->default('-'),

                TextColumn::make('paymentMethod.payment_type')
                    ->label('طريقة الدفع')
                    ->sortable()
                    ->default('-'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
