<?php

namespace App\Filament\Resources\WeekResults;

use App\Filament\Resources\WeekResults\Pages\CreateWeekResult;
use App\Filament\Resources\WeekResults\Pages\EditWeekResult;
use App\Filament\Resources\WeekResults\Pages\ListWeekResults;
use App\Filament\Resources\WeekResults\Pages\ViewWeekResult;
use App\Filament\Resources\WeekResults\Schemas\WeekResultForm;
use App\Filament\Resources\WeekResults\Schemas\WeekResultInfolist;
use App\Filament\Resources\WeekResults\Tables\WeekResultsTable;
use App\Models\WeekResult;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WeekResultResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = WeekResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'نتائج الأسابيع';
    protected static string|UnitEnum|null $navigationGroup = 'التقييم والدرجات';
    protected static ?string $pluralModelLabel = 'نتائج الأسابيع';
    protected static ?string $modelLabel = 'نتيجة أسبوع';

    protected static function getResourcePermissionName(): string
    {
        return 'week-results';
    }

    public static function form(Schema $schema): Schema
    {
        return WeekResultForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WeekResultInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeekResultsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWeekResults::route('/'),
            'create' => CreateWeekResult::route('/create'),
            'view' => ViewWeekResult::route('/{record}'),
            'edit' => EditWeekResult::route('/{record}/edit'),
        ];
    }
}
