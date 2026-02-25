<?php

namespace App\Filament\Resources\InstallmentTypes;

use App\Filament\Resources\InstallmentTypes\Pages\CreateInstallmentType;
use App\Filament\Resources\InstallmentTypes\Pages\EditInstallmentType;
use App\Filament\Resources\InstallmentTypes\Pages\ListInstallmentTypes;
use App\Filament\Resources\InstallmentTypes\Pages\ViewInstallmentType;
use App\Filament\Resources\InstallmentTypes\Schemas\InstallmentTypeForm;
use App\Filament\Resources\InstallmentTypes\Schemas\InstallmentTypeInfolist;
use App\Filament\Resources\InstallmentTypes\Tables\InstallmentTypesTable;
use App\Models\InstallmentType;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class InstallmentTypeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = InstallmentType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'installment_type_name';
    protected static ?string $navigationLabel = 'أنواع الأقساط';

        protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';


    protected static ?string $pluralModelLabel = 'أنواع الأقساط';

    protected static ?string $modelLabel = 'نوع قسط';

    protected static function getResourcePermissionName(): string
    {
        return 'installment_types';
    }

    public static function form(Schema $schema): Schema
    {
        return InstallmentTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstallmentTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstallmentTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstallmentTypes::route('/'),
            'create' => CreateInstallmentType::route('/create'),
            'view' => ViewInstallmentType::route('/{record}'),
            'edit' => EditInstallmentType::route('/{record}/edit'),
        ];
    }
}
