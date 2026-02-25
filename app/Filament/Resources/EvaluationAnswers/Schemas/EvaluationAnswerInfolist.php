<?php

namespace App\Filament\Resources\EvaluationAnswers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EvaluationAnswerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('question.label')
                    ->label('السؤال'),
                TextEntry::make('label')
                    ->label('الإجابة'),
                IconEntry::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->placeholder('-'),
            ]);
    }
}
