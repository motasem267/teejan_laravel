<?php

namespace App\Filament\Resources\Installments\Schemas;

use App\Models\academic_years;
use App\Models\AnnualSubscriptionFee;
use App\Models\Installment;
use App\Models\InstallmentType;
use App\Models\ParentModel;
use App\Models\StudentEnrollment;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class InstallmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_year')
                    ->label('السنة الدراسية')
                    ->options(academic_years::pluck('year_label', 'id'))
                    ->default(academic_years::getActiveId())
                    ->required()
                    ->searchable()
                    ->live()
                    ->preload()
                    ->columnSpanFull(),
                
                Select::make('parent_id')
                    ->label('ولي الأمر')
                    ->options(function (Get $get) {
                        $academicYearId = $get('academic_year');
                        
                        if (!$academicYearId) {
                            return [];
                        }
                        
                        // جلب أولياء الأمور الذين لديهم أبناء مسجلين في هذه السنة
                        $parentIds = StudentEnrollment::where('academic_year_id', $academicYearId)
                            ->with('student')
                            ->get()
                            ->pluck('student.parent_id')
                            ->unique()
                            ->filter();
                        
                        return ParentModel::whereIn('id', $parentIds)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->live()
                    ->preload()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('parent_selected', $state))
                    ->disabled(fn (Get $get) => !$get('academic_year'))
                    ->columnSpanFull()
                    ->helperText('يرجى اختيار السنة الدراسية أولاً'),
                
                Section::make('')
                    ->schema([
                        Placeholder::make('total_fees_display')
                            ->label('إجمالي الأقساط')
                            ->content(function (Get $get) {
                                $parentId = $get('parent_id');
                                $academicYearId = $get('academic_year');
                                
                                if (!$parentId || !$academicYearId) {
                                    return '0.00 دينار';
                                }
                                
                                $total = self::calculateTotalFees($parentId, $academicYearId);
                                return number_format($total, 2) . ' دينار';
                            }),
                        
                        Placeholder::make('paid_fees_display')
                            ->label('الأقساط المدفوعة')
                            ->content(function (Get $get) {
                                $parentId = $get('parent_id');
                                $academicYearId = $get('academic_year');
                                
                                if (!$parentId || !$academicYearId) {
                                    return '0.00 دينار';
                                }
                                
                                $paid = self::calculatePaidFees($parentId, $academicYearId);
                                return number_format($paid, 2) . ' دينار';
                            }),
                        
                        Placeholder::make('remaining_fees_display')
                            ->label('المتبقي')
                            ->content(function (Get $get) {
                                $parentId = $get('parent_id');
                                $academicYearId = $get('academic_year');
                                
                                if (!$parentId || !$academicYearId) {
                                    return '0.00 دينار';
                                }
                                
                                $total = self::calculateTotalFees($parentId, $academicYearId);
                                $paid = self::calculatePaidFees($parentId, $academicYearId);
                                $remaining = $total - $paid;
                                
                                return number_format($remaining, 2) . ' دينار';
                            }),
                    ])
                    ->visible(fn (Get $get) => $get('parent_id') !== null && $get('academic_year') !== null)
                    ->columns(3)
                    ->columnSpanFull()
                    ->compact(),

                Section::make('ابناء ولي الأمر')
                    ->schema([
                        Placeholder::make('الابناء')
                            ->label('')
                            ->content(function (Get $get): HtmlString {
                                $parentId = $get('parent_id');
                                $academicYearId = $get('academic_year');

                                if (!$parentId) {
                                    return new HtmlString('');
                                }

                                $parent = ParentModel::with(['students' => function ($q) use ($academicYearId) {
                                    $q->with(['enrollments' => function ($eq) use ($academicYearId) {
                                        if ($academicYearId) {
                                            $eq->where('academic_year_id', $academicYearId)->with('grade');
                                        } else {
                                            $eq->with('grade');
                                        }
                                    }]);
                                }])->find($parentId);

                                if (!$parent || $parent->students->isEmpty()) {
                                    return new HtmlString(
                                        '<span class="text-sm text-gray-400 dark:text-gray-500">لا يوجد طلاب مرتبطون بهذا ولي الأمر</span>'
                                    );
                                }

                                $rows = $parent->students->map(function ($s) use ($academicYearId) {
                                    $enrollment = $s->enrollments->first();
                                    $gradeName = $enrollment?->grade?->name ?? '—';
                                    $fee = null;
                                    if ($enrollment && $enrollment->grade_id && $academicYearId) {
                                        $fee = AnnualSubscriptionFee::where('grade_id', $enrollment->grade_id)
                                            ->where('academic_year_id', $academicYearId)
                                            ->first();
                                    }
                                    $amount = $fee ? number_format($fee->amount, 2) . ' دينار' : '—';

                                    return '<tr style="border-bottom: 1px solid #e5e7eb;">'
                                        . '<td style="padding: 12px 20px; font-size: 14px; border-left: 1px solid #e5e7eb;">' . e($s->full_name) . '</td>'
                                        . '<td style="padding: 12px 20px; font-size: 14px; text-align: center; border-left: 1px solid #e5e7eb;">' . e($gradeName) . '</td>'
                                        . '<td style="padding: 12px 20px; font-size: 14px; text-align: center; font-weight: 600;">' . $amount . '</td>'
                                        . '</tr>';
                                })->implode('');

                                return new HtmlString(
                                    '<div style="overflow:hidden; border-radius:12px; border:1px solid #e5e7eb;">'
                                    . '<table style="width:100%; border-collapse:collapse; direction:rtl;">'
                                    . '<thead>'
                                    . '<tr style="background:#f9fafb; border-bottom:2px solid #e5e7eb;">'
                                    . '<th style="padding:12px 20px; font-size:12px; font-weight:600; color:#6b7280; text-align:right; border-left:1px solid #e5e7eb;">اسم الطالب</th>'
                                    . '<th style="padding:12px 20px; font-size:12px; font-weight:600; color:#6b7280; text-align:center; border-left:1px solid #e5e7eb;">الصف</th>'
                                    . '<th style="padding:12px 20px; font-size:12px; font-weight:600; color:#6b7280; text-align:center;">القيمة</th>'
                                    . '</tr>'
                                    . '</thead>'
                                    . '<tbody>'
                                    . $rows
                                    . '</tbody>'
                                    . '</table>'
                                    . '</div>'
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get): bool => filled($get('parent_id')))
                    ->columnSpanFull()
                    ->compact(),
                
                Select::make('installment_type_id')
                    ->label('نوع القسط')
                    ->relationship('installmentType', 'installment_type_name')
                    ->default(function () {
                        return InstallmentType::where('installment_type_name', 'like', '%قسط سنوي%')
                            ->orWhere('installment_type_name', 'like', '%سنوي%')
                            ->first()?->id;
                    })
                    
                    ->dehydrated()
                    ->required(),
                
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->suffix('دينار')
                    ->live(onBlur: true)
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $parentId = $get('parent_id');
                            $academicYearId = $get('academic_year');
                            
                            if (!$parentId) {
                                $fail('الرجاء اختيار ولي الأمر أولاً');
                                return;
                            }
                            
                            if (!$academicYearId) {
                                $fail('الرجاء اختيار السنة الدراسية أولاً');
                                return;
                            }
                            
                            $total = self::calculateTotalFees($parentId, $academicYearId);
                            $paid = self::calculatePaidFees($parentId, $academicYearId);
                            $remaining = $total - $paid;
                            
                            if ($value > $remaining) {
                                $fail('المبلغ المدخل (' . number_format($value, 2) . ' دينار) أكبر من المبلغ المتبقي (' . number_format($remaining, 2) . ' دينار)');
                            }
                        },
                    ]),
                
                Select::make('payment_type_id')
                    ->label('طريقة الدفع')
                    ->relationship('paymentMethod', 'payment_type')
                    ->searchable()
                    ->preload(),
                
                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
    
    /**
     * حساب إجمالي الأقساط لولي الأمر بناءً على رسوم الاشتراك السنوي لأبنائه في سنة دراسية محددة
     */
    private static function calculateTotalFees(int $parentId, int $academicYearId): float
    {
        $parent = ParentModel::with(['students' => function($query) use ($academicYearId) {
            $query->whereHas('enrollments', function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            })->with(['enrollments' => function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            }]);
        }])->find($parentId);
        
        if (!$parent || $parent->students->isEmpty()) {
            return 0;
        }
        
        $totalFees = 0;
        
        foreach ($parent->students as $student) {
            // الحصول على قيد الطالب في هذه السنة
            $enrollment = $student->enrollments->first();
            
            if ($enrollment && $enrollment->grade_id) {
                // جلب رسوم الاشتراك السنوي للصف في هذه السنة
                $fee = AnnualSubscriptionFee::where('grade_id', $enrollment->grade_id)
                    ->where('academic_year_id', $academicYearId)
                    ->first();
                
                if ($fee) {
                    $totalFees += $fee->amount;
                }
            }
        }
        
        return $totalFees;
    }
    
    /**
     * حساب الأقساط المدفوعة لولي الأمر في سنة دراسية محددة (فقط الأقساط السنوية وليس الاشتراك)
     */
    private static function calculatePaidFees(int $parentId, int $academicYearId): float
    {
        // الحصول على ID نوع القسط السنوي (وليس قسط الاشتراك)
        $annualInstallmentTypes = InstallmentType::where('installment_type_name', 'like', '%سنوي%')
            ->orWhere('installment_type_name', 'like', '%قسط%')
            ->where('installment_type_name', 'NOT LIKE', '%اشتراك%')
            ->pluck('id');
        
        $paidFees = Installment::where('parent_id', $parentId)
            ->where('academic_year', $academicYearId)
            ->whereIn('installment_type_id', $annualInstallmentTypes)
            ->sum('amount');
        
        return $paidFees ?? 0;
    }
}
