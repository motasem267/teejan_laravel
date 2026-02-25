<?php

namespace App\Filament\Resources\DeductionTypes;

use App\Filament\Resources\DeductionTypes\Pages\CreateDeductionType;
use App\Filament\Resources\DeductionTypes\Pages\EditDeductionType;
use App\Filament\Resources\DeductionTypes\Pages\ListDeductionTypes;
use App\Filament\Resources\DeductionTypes\Pages\ViewDeductionType;
use App\Filament\Resources\DeductionTypes\Schemas\DeductionTypeForm;
use App\Filament\Resources\DeductionTypes\Schemas\DeductionTypeInfolist;
use App\Filament\Resources\DeductionTypes\Tables\DeductionTypesTable;
use App\Models\DeductionType;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class DeductionTypeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = DeductionType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'deduction_type_name';
    protected static ?string $navigationLabel = 'أنواع الخصومات';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';
    protected static ?string $pluralModelLabel = 'أنواع الخصومات';
    protected static ?string $modelLabel = 'نوع خصم';

    protected static function getResourcePermissionName(): string
    {
        return 'deduction_types';
    }

    public static function form(Schema $schema): Schema
    {
        return DeductionTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DeductionTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeductionTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeductionTypes::route('/'),
            'create' => CreateDeductionType::route('/create'),
            'view' => ViewDeductionType::route('/{record}'),
            'edit' => EditDeductionType::route('/{record}/edit'),
        ];
    }
}
