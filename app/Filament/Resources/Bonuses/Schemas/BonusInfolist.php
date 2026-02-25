<?php

namespace App\Filament\Resources\Bonuses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BonusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('employee.name')
                    ->label('الموظف'),
                TextEntry::make('bonusType.bonus_type_name')
                    ->label('نوع العلاوة'),
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
