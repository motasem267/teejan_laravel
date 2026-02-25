<?php

namespace App\Filament\Resources\Deductions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DeductionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('employee.name')
                    ->label('الموظف'),
                TextEntry::make('deductionType.deduction_type_name')
                    ->label('نوع الخصم'),
                TextEntry::make('amount')
                    ->label('المبلغ')
                    ->money('USD'),
                TextEntry::make('date')
                    ->label('التاريخ')
                    ->date(),
                TextEntry::make('remark')
                    ->label('ملاحظات'),
                TextEntry::make('creator.name')
                    ->label('تم الإنشاء بواسطة'),
            ]);
    }
}
