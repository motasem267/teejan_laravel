<?php

namespace App\Filament\Resources\Installments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InstallmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('parent.name')
                    ->label('ولي الأمر'),
                TextEntry::make('installmentType.installment_type_name')
                    ->label('نوع القسط'),
                TextEntry::make('amount')
                    ->label('المبلغ')
                    ->money('USD'),
                TextEntry::make('description')
                    ->label('الوصف'),
                TextEntry::make('paymentMethod.payment_type')
                    ->label('طريقة الدفع'),
                TextEntry::make('academic_year')
                    ->label('السنة الدراسية'),
                TextEntry::make('creator.name')
                    ->label('تم الإنشاء بواسطة'),
            ]);
    }
}
