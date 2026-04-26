<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('description')
                    ->label('الوصف'),
                TextEntry::make('expensesType.expenses_type_name')
                    ->label('نوع المصروف'),
                TextEntry::make('amount')
                    ->label('المبلغ')
                    ->money('USD'),
                TextEntry::make('paymentMethod.payment_type')
                    ->label('طريقة الدفع'),
                TextEntry::make('date')
                    ->label('التاريخ')
                    ->date(),
                TextEntry::make('creator.name')
                    ->label('تم الإنشاء بواسطة'),
                TextEntry::make('updater.name')
                    ->label('تم التحديث بواسطة'),
            ]);
    }
}
