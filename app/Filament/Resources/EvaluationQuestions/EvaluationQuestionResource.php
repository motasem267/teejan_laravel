<?php

namespace App\Filament\Resources\EvaluationQuestions;

use App\Filament\Resources\EvaluationQuestions\Pages\CreateEvaluationQuestion;
use App\Filament\Resources\EvaluationQuestions\Pages\EditEvaluationQuestion;
use App\Filament\Resources\EvaluationQuestions\Pages\ListEvaluationQuestions;
use App\Filament\Resources\EvaluationQuestions\Pages\ViewEvaluationQuestion;
use App\Filament\Resources\EvaluationQuestions\Schemas\EvaluationQuestionForm;
use App\Filament\Resources\EvaluationQuestions\Schemas\EvaluationQuestionInfolist;
use App\Filament\Resources\EvaluationQuestions\Tables\EvaluationQuestionsTable;
use App\Models\EvaluationQuestion;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class EvaluationQuestionResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = EvaluationQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'EvaluationQuestion';
    protected static ?string $navigationLabel = 'اسئلة التقييم';
    protected static string|UnitEnum|null $navigationGroup = 'التقييم والدرجات';

    protected static ?string $modelLabel = 'سؤال تقييم';

    protected static ?string $pluralModelLabel = 'اسئلة التقييم';

    
    protected static function getResourcePermissionName(): string
    {
        return 'evaluation-questions';
    }

    public static function form(Schema $schema): Schema
    {
        return EvaluationQuestionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EvaluationQuestionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EvaluationQuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvaluationQuestions::route('/'),
            'create' => CreateEvaluationQuestion::route('/create'),
            'view' => ViewEvaluationQuestion::route('/{record}'),
            'edit' => EditEvaluationQuestion::route('/{record}/edit'),
        ];
    }
}
