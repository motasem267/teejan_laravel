<?php

namespace App\Filament\Resources\Parents;

use App\Filament\Resources\Parents\Pages\CreateParent;
use App\Filament\Resources\Parents\Pages\EditParent;
use App\Filament\Resources\Parents\Pages\ListParents;
use App\Filament\Resources\Parents\Pages\ViewParent;
use App\Filament\Resources\Parents\Schemas\ParentForm;
use App\Filament\Resources\Parents\Schemas\ParentInfolist;
use App\Filament\Resources\Parents\Tables\ParentsTable;
use App\Models\ParentModel;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class ParentResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = ParentModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string|UnitEnum|null $navigationGroup = 'إدارة الطلاب وأولياء الامور';
    protected static ?string $pluralModelLabel = 'أولياء الأمور';
    protected static ?string $modelLabel = 'ولي أمر';

    protected static function getResourcePermissionName(): string
    {
        return 'parents';
    }

    public static function form(Schema $schema): Schema
    {
        return ParentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ParentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParents::route('/'),
            'create' => CreateParent::route('/create'),
            'view' => ViewParent::route('/{record}'),
            'edit' => EditParent::route('/{record}/edit'),
        ];
    }
}
