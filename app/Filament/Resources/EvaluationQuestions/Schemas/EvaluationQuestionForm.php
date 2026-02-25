<?php

namespace App\Filament\Resources\EvaluationQuestions\Schemas;

use App\Models\EvaluationAnswer;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EvaluationQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('evaluation_type_id')
                    ->label('نوع التقييم')
                    ->relationship('evaluationType', 'label')
                    ->required()
                    ->searchable()
                    ->preload(),
                Textarea::make('label')
                    ->label('السؤال')
                    ->required()
                    ->maxLength(200)
                    ->rows(3)
                    ->autosize()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                
                CheckboxList::make('sharedAnswers')
                    ->label('الإجابات المتاحة للسؤال')
                    ->relationship('sharedAnswers', 'label')
                    ->options(
                        EvaluationAnswer::where('is_active', true)
                            ->pluck('label', 'id')
                    )
                    ->columns(2)
                    ->gridDirection('row')
                    ->bulkToggleable()
                    ->searchable(),
            ]);
    }
}
