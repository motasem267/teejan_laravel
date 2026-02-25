<?php

namespace App\Filament\Resources\SalaryTypes;

use App\Filament\Resources\SalaryTypes\Pages\CreateSalaryType;
use App\Filament\Resources\SalaryTypes\Pages\EditSalaryType;
use App\Filament\Resources\SalaryTypes\Pages\ListSalaryTypes;
use App\Filament\Resources\SalaryTypes\Pages\ViewSalaryType;
use App\Filament\Resources\SalaryTypes\Schemas\SalaryTypeForm;
use App\Filament\Resources\SalaryTypes\Schemas\SalaryTypeInfolist;
use App\Filament\Resources\SalaryTypes\Tables\SalaryTypesTable;
use App\Models\SalaryType;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class SalaryTypeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = SalaryType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'أنواع الرواتب';

    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';

    protected static ?string $pluralModelLabel = 'أنواع الرواتب';

    protected static ?string $modelLabel = 'نوع الراتب';

    
    protected static function getResourcePermissionName(): string
    {
        return 'salary-types';
    }

    public static function form(Schema $schema): Schema
    {
        return SalaryTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SalaryTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalaryTypesTable::configure($table);
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
            'index' => ListSalaryTypes::route('/'),
            'create' => CreateSalaryType::route('/create'),
            'view' => ViewSalaryType::route('/{record}'),
            'edit' => EditSalaryType::route('/{record}/edit'),
        ];
    }
}
