<?php

namespace App\Filament\Resources\ExpensesTypes;

use App\Filament\Resources\ExpensesTypes\Pages\CreateExpensesType;
use App\Filament\Resources\ExpensesTypes\Pages\EditExpensesType;
use App\Filament\Resources\ExpensesTypes\Pages\ListExpensesTypes;
use App\Filament\Resources\ExpensesTypes\Pages\ViewExpensesType;
use App\Filament\Resources\ExpensesTypes\Schemas\ExpensesTypeForm;
use App\Filament\Resources\ExpensesTypes\Schemas\ExpensesTypeInfolist;
use App\Filament\Resources\ExpensesTypes\Tables\ExpensesTypesTable;
use App\Models\ExpensesType;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class ExpensesTypeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = ExpensesType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'expenses_type_name';
    protected static ?string $navigationLabel = 'أنواع المصروفات';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';
    protected static ?string $pluralModelLabel = 'أنواع المصروفات';
    protected static ?string $modelLabel = 'نوع مصروف';

    protected static function getResourcePermissionName(): string
    {
        return 'expenses_types';
    }

    public static function form(Schema $schema): Schema
    {
        return ExpensesTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExpensesTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpensesTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpensesTypes::route('/'),
            'create' => CreateExpensesType::route('/create'),
            'view' => ViewExpensesType::route('/{record}'),
            'edit' => EditExpensesType::route('/{record}/edit'),
        ];
    }
}
