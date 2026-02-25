<?php

namespace App\Filament\Resources\EvaluationTypes;

use App\Filament\Resources\EvaluationTypes\Pages\CreateEvaluationType;
use App\Filament\Resources\EvaluationTypes\Pages\EditEvaluationType;
use App\Filament\Resources\EvaluationTypes\Pages\ListEvaluationTypes;
use App\Filament\Resources\EvaluationTypes\Pages\ViewEvaluationType;
use App\Filament\Resources\EvaluationTypes\Schemas\EvaluationTypeForm;
use App\Filament\Resources\EvaluationTypes\Schemas\EvaluationTypeInfolist;
use App\Filament\Resources\EvaluationTypes\Tables\EvaluationTypesTable;
use App\Models\EvaluationType;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class EvaluationTypeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = EvaluationType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'EvaluationType';
    protected static ?string $navigationLabel = 'أنواع التقييم';
    protected static string|UnitEnum|null $navigationGroup = 'التقييم والدرجات';
    
    protected static ?string $modelLabel = 'نوع التقييم';
    
    protected static ?string $pluralModelLabel = 'أنواع التقييم';

    
    protected static function getResourcePermissionName(): string
    {
        return 'evaluation-types';
    }

    public static function form(Schema $schema): Schema
    {
        return EvaluationTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EvaluationTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EvaluationTypesTable::configure($table);
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
            'index' => ListEvaluationTypes::route('/'),
            'create' => CreateEvaluationType::route('/create'),
            'view' => ViewEvaluationType::route('/{record}'),
            'edit' => EditEvaluationType::route('/{record}/edit'),
        ];
    }
}
