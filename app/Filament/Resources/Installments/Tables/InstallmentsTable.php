<?php

namespace App\Filament\Resources\Installments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstallmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parent.name')
                    ->label('ولي الأمر')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('installmentType.installment_type_name')
                    ->label('نوع القسط')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('paymentMethod.payment_type')
                    ->label('طريقة الدفع')
                    ->searchable(),
                TextColumn::make('academic_year')
                    ->label('السنة الدراسية')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->label('تم الإنشاء بواسطة')
                    ->searchable(),
            ])
            ->filters([])
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
