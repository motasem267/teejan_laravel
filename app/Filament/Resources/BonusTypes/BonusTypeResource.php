<?php

namespace App\Filament\Resources\BonusTypes;

use App\Filament\Resources\BonusTypes\Pages\CreateBonusType;
use App\Filament\Resources\BonusTypes\Pages\EditBonusType;
use App\Filament\Resources\BonusTypes\Pages\ListBonusTypes;
use App\Filament\Resources\BonusTypes\Pages\ViewBonusType;
use App\Filament\Resources\BonusTypes\Schemas\BonusTypeForm;
use App\Filament\Resources\BonusTypes\Schemas\BonusTypeInfolist;
use App\Filament\Resources\BonusTypes\Tables\BonusTypesTable;
use App\Models\BonusType;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class BonusTypeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = BonusType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'bonus_type_name';
    protected static ?string $navigationLabel = 'أنواع العلاوات';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';
    protected static ?string $pluralModelLabel = 'أنواع العلاوات';
    protected static ?string $modelLabel = 'نوع علاوة';

    protected static function getResourcePermissionName(): string
    {
        return 'bonus_types';
    }

    public static function form(Schema $schema): Schema
    {
        return BonusTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BonusTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BonusTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBonusTypes::route('/'),
            'create' => CreateBonusType::route('/create'),
            'view' => ViewBonusType::route('/{record}'),
            'edit' => EditBonusType::route('/{record}/edit'),
        ];
    }
}
