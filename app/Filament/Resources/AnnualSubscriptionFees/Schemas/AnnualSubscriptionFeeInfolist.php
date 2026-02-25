<?php

namespace App\Filament\Resources\AnnualSubscriptionFees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AnnualSubscriptionFeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('grade.name')
                    ->label('الصف'),
                TextEntry::make('academicYear.year_label')
                    ->label('السنة الدراسية'),
                TextEntry::make('amount')
                    ->label('المبلغ')
                    ->money('USD'),
            ]);
    }
}
