<?php

namespace App\Filament\Resources\Deductions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeductionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('emp_id')
                    ->label('الموظف')
                    // الموظفين المقيدين في السنة الفعالة (مع إبقاء الموظف الحالي عند التعديل)
                    ->relationship('employee', 'name', fn ($query, $record) => $query->enrolledIn(null, $record?->emp_id))
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('deduction_type_id')
                    ->label('نوع الخصم')
                    ->relationship('deductionType', 'deduction_type_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now()),
                Textarea::make('remark')
                    ->label('ملاحظات')
                    ->rows(3),
            ]);
    }
}
