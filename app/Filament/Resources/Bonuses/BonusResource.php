<?php

namespace App\Filament\Resources\Bonuses;

use App\Filament\Resources\Bonuses\Pages\CreateBonus;
use App\Filament\Resources\Bonuses\Pages\EditBonus;
use App\Filament\Resources\Bonuses\Pages\ListBonuses;
use App\Filament\Resources\Bonuses\Pages\ViewBonus;
use App\Filament\Resources\Bonuses\Schemas\BonusForm;
use App\Filament\Resources\Bonuses\Schemas\BonusInfolist;
use App\Filament\Resources\Bonuses\Tables\BonusesTable;
use App\Models\Bonus;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class BonusResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Bonus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'العلاوات';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';

    protected static ?string $pluralModelLabel = 'العلاوات';

    protected static ?string $modelLabel = 'علاوة';

    protected static function getResourcePermissionName(): string
    {
        return 'bonuses';
    }

    public static function form(Schema $schema): Schema
    {
        return BonusForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BonusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BonusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBonuses::route('/'),
            'create' => CreateBonus::route('/create'),
            'view' => ViewBonus::route('/{record}'),
            'edit' => EditBonus::route('/{record}/edit'),
        ];
    }
}
