<?php

namespace App\Filament\Resources\EvaluationAnswers;

use App\Filament\Resources\EvaluationAnswers\Pages\CreateEvaluationAnswer;
use App\Filament\Resources\EvaluationAnswers\Pages\EditEvaluationAnswer;
use App\Filament\Resources\EvaluationAnswers\Pages\ListEvaluationAnswers;
use App\Filament\Resources\EvaluationAnswers\Pages\ViewEvaluationAnswer;
use App\Filament\Resources\EvaluationAnswers\Schemas\EvaluationAnswerForm;
use App\Filament\Resources\EvaluationAnswers\Schemas\EvaluationAnswerInfolist;
use App\Filament\Resources\EvaluationAnswers\Tables\EvaluationAnswersTable;
use App\Models\EvaluationAnswer;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class EvaluationAnswerResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = EvaluationAnswer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'EvaluationAnswer';
    protected static ?string $navigationLabel = 'إجابات التقييم';
    protected static string|UnitEnum|null $navigationGroup = 'التقييم والدرجات';

    protected static ?string $modelLabel = 'إجابة تقييم';

    protected static ?string $pluralModelLabel = 'إجابات التقييم';

    protected static function getResourcePermissionName(): string
    {
        return 'evaluation-answers';
    }

    public static function form(Schema $schema): Schema
    {
        return EvaluationAnswerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EvaluationAnswerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EvaluationAnswersTable::configure($table);
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
            'index' => ListEvaluationAnswers::route('/'),
            'create' => CreateEvaluationAnswer::route('/create'),
            'view' => ViewEvaluationAnswer::route('/{record}'),
            'edit' => EditEvaluationAnswer::route('/{record}/edit'),
        ];
    }
}
