<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\academic_years;
use App\Models\InstallmentType;
use App\Models\PaymentMethod;
use App\Models\StudentStatus;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('رقم الطالب')
                    ->numeric()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->minValue(1)
                    ->helperText('رقم تعريف الطالب في النظام'),
                    
                TextInput::make('full_name')
                    ->label('الاسم الكامل')
                    ->required()
                    ->maxLength(200),
                    
                TextInput::make('national_id')
                    ->label('الرقم الوطني')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                    
                Select::make('parent_id')
                    ->label('ولي الأمر')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->placeholder('اختر ولي الأمر'),

                Select::make('status_id')
                    ->label('حالة الطالب')
                    ->relationship('status', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('اختر حالة الطالب')
                    ->helperText('حالة الطالب الحالية في النظام'),

                TextInput::make('mother_phone')
                    ->label('هاتف الأم')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),
                    
                Radio::make('has_subscription_fee')
                    ->label('رسوم الاشتراك')
                    ->boolean('برسوم اشتراك', 'بدون رسوم اشتراك')
                    ->inline()
                    ->live()
                    ->default(false)
                    ->columnSpanFull(),
                    
                Section::make('معلومات القسط')
                    ->schema([
                        Select::make('academic_year')
                            ->label('السنة الدراسية')
                            ->options(academic_years::pluck('year_label', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Select::make('installment_type_id')
                            ->label('نوع القسط')
                            ->options(InstallmentType::pluck('installment_type_name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(function () {
                                return InstallmentType::where('installment_type_name', 'like', '%قسط اشتراك%')
                                    ->orWhere('installment_type_name', 'like', '%اشتراك%')
                                    ->first()?->id;
                            })
                            ->disabled()
                            ->dehydrated(),
                        
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('دينار'),
                        
                        Select::make('payment_type_id')
                            ->label('طريقة الدفع')
                            ->options(PaymentMethod::pluck('payment_type', 'id'))
                            ->searchable()
                            ->preload(),
                        
                        Textarea::make('installment_description')
                            ->label('وصف القسط')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->hidden(fn (Get $get) => $get('has_subscription_fee') !== true),
            ]);
    }
}
