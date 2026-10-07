<?php

namespace App\Filament\Resources\EmployeeEnrollments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

/**
 * نموذج تعديل قيد موظف (الإضافة تتم من صفحة القيد الجماعي).
 */
class EmployeeEnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('الموظف')
                    ->relationship('employee', 'name')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('academic_year_id')
                    ->label('السنة الدراسية')
                    ->relationship('academicYear', 'year_label')
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule, $record) => $rule->where('employee_id', $record?->employee_id),
                    )
                    ->validationMessages(['unique' => 'الموظف مقيد مسبقاً في هذه السنة']),

                Select::make('emp_type_id')
                    ->label('الوظيفة')
                    ->relationship('employeeType', 'type_name')
                    ->preload()
                    ->required(),
            ]);
    }
}
