<?php

namespace App\Filament\Resources\Salaries\Schemas;

use App\Models\Bonus;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\WorkDaysCalendar;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SalaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('emp_id')
                    ->label('الموظف')
                    ->relationship('employee', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $employee = Employee::find($state);
                            if ($employee) {
                                $set('basic_salary', $employee->salary ?? 0);
                            }
                        }
                    })
                    ->validationMessages([
                        'required' => 'يجب اختيار الموظف',
                    ]),

                Select::make('month')
                    ->label('الشهر')
                    ->required()
                    ->options([
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
                    ])
                    ->default(now()->month)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::calculateBonusesAndDeductions($set, $get);
                    })
                    ->validationMessages([
                        'required' => 'يجب اختيار الشهر',
                    ]),

                TextInput::make('year')
                    ->label('السنة')
                    ->numeric()
                    ->required()
                    ->minValue(2000)
                    ->maxValue(2100)
                    ->default(now()->year)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::calculateBonusesAndDeductions($set, $get);
                    })
                    ->validationMessages([
                        'required' => 'يجب إدخال السنة',
                        'numeric' => 'يجب أن تكون السنة رقماً',
                        'min' => 'السنة يجب أن تكون أكبر من أو تساوي 2000',
                        'max' => 'السنة يجب أن تكون أقل من أو تساوي 2100',
                    ]),

                TextInput::make('basic_salary')
                    ->label('الراتب الأساسي')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('bonus_amount')
                    ->label('إجمالي الحوافز')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('deduction_amount')
                    ->label('إجمالي الخصومات')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('sessions_count')
                    ->label('إجمالي عدد الحصص')
                    ->numeric()
                    ->required(fn (callable $get) => self::isTeacher($get('emp_id')))
                    ->minValue(0)
                    ->integer()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::calculateNetSalary($set, $get);
                    })
                    ->visible(fn (callable $get) => self::isTeacher($get('emp_id')))
                    ->validationMessages([
                        'required' => 'يجب إدخال عدد الحصص',
                        'numeric' => 'يجب أن يكون عدد الحصص رقماً',
                        'min' => 'عدد الحصص يجب أن يكون صفراً أو أكبر',
                        'integer' => 'عدد الحصص يجب أن يكون عدداً صحيحاً',
                    ]),

                TextInput::make('attendance_days')
                    ->label('عدد أيام الحضور')
                    ->numeric()
                    ->required(fn (callable $get) => !self::isTeacher($get('emp_id')))
                    ->minValue(0)
                    ->integer()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::calculateNetSalary($set, $get);
                    })
                    ->visible(fn (callable $get) => !self::isTeacher($get('emp_id')))
                    ->validationMessages([
                        'required' => 'يجب إدخال عدد أيام الحضور',
                        'numeric' => 'يجب أن يكون عدد الأيام رقماً',
                        'min' => 'عدد الأيام يجب أن يكون صفراً أو أكبر',
                        'integer' => 'عدد الأيام يجب أن يكون عدداً صحيحاً',
                    ]),

                TextInput::make('net_salary')
                    ->label('الراتب الصافي')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                DatePicker::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->nullable(),

                Select::make('payment_type_id')
                    ->label('طريقة الدفع')
                    ->relationship('paymentMethod', 'payment_type')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    protected static function calculateBonusesAndDeductions(callable $set, callable $get): void
    {
        $empId = $get('emp_id');
        $month = $get('month');
        $year = $get('year');

        if (!$empId || !$month || !$year) {
            return;
        }

        // حساب أول وآخر يوم في الشهر
        $startDate = date('Y-m-d', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

        // حساب إجمالي الحوافز
        $bonusTotal = Bonus::where('emp_id', $empId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // حساب إجمالي الخصومات
        $deductionTotal = Deduction::where('emp_id', $empId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $set('bonus_amount', number_format($bonusTotal, 2, '.', ''));
        $set('deduction_amount', number_format($deductionTotal, 2, '.', ''));

        // حساب الراتب الصافي
        self::calculateNetSalary($set, $get);
    }

    protected static function calculateNetSalary(callable $set, callable $get): void
    {
        $empId = $get('emp_id');
        $basicSalary = floatval($get('basic_salary') ?? 0);
        $bonusAmount = floatval($get('bonus_amount') ?? 0);
        $deductionAmount = floatval($get('deduction_amount') ?? 0);
        $month = $get('month');
        $year = $get('year');

        if (!$empId) {
            return;
        }

        $netSalary = 0;

        // التحقق إذا كان الموظف معلم
        if (self::isTeacher($empId)) {
            // للمعلم: الراتب الأساسي × عدد الحصص + الحوافز - الخصومات
            $sessionsCount = intval($get('sessions_count') ?? 0);
            $netSalary = ($basicSalary * $sessionsCount) + $bonusAmount - $deductionAmount;
        } else {
            // للموظف العادي: (عدد أيام الحضور / إجمالي أيام الدوام من الجدول) × الراتب الأساسي + الحوافز - الخصومات
            $attendanceDays = intval($get('attendance_days') ?? 0);
            
            // قراءة عدد أيام الدوام من جدول work_days_calendar
            if ($month && $year) {
                $workDaysRecord = WorkDaysCalendar::where('month', $month)
                    ->where('year', $year)
                    ->first();
                
                if ($workDaysRecord && $workDaysRecord->work_days > 0) {
                    $totalWorkDays = $workDaysRecord->work_days;
                    $netSalary = (($attendanceDays / $totalWorkDays) * $basicSalary) + $bonusAmount - $deductionAmount;
                } else {
                    // إذا لم يتم تحديد أيام الدوام في الجدول، نستخدم الراتب الكامل
                    $netSalary = $basicSalary + $bonusAmount - $deductionAmount;
                }
            } else {
                $netSalary = $basicSalary + $bonusAmount - $deductionAmount;
            }
        }

        $set('net_salary', number_format($netSalary, 2, '.', ''));
    }

    protected static function isTeacher(?int $empId): bool
    {
        if (!$empId) {
            return false;
        }

        $employee = Employee::find($empId);
        if (!$employee) {
            return false;
        }

        // نفترض أن نوع الموظف المعلم له employee_type_id = 1 أو اسم معين
        // قم بتعديل هذا الشرط حسب قاعدة بياناتك
        return $employee->employeeType?->employee_type_name === 'معلم';
    }
}
