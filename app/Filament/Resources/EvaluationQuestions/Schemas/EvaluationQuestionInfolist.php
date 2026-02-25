<?php

namespace App\Filament\Resources\EvaluationQuestions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EvaluationQuestionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('evaluationType.label')
                    ->label('نوع التقييم'),
                TextEntry::make('label')
                    ->label('السؤال')
                    ->columnSpanFull(),
                IconEntry::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->placeholder('-'),
            ]);
    }
}
