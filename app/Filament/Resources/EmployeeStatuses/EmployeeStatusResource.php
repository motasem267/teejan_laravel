<?php

namespace App\Filament\Resources\EmployeeStatuses;

use App\Filament\Resources\EmployeeStatuses\Pages\CreateEmployeeStatus;
use App\Filament\Resources\EmployeeStatuses\Pages\EditEmployeeStatus;
use App\Filament\Resources\EmployeeStatuses\Pages\ListEmployeeStatuses;
use App\Filament\Resources\EmployeeStatuses\Pages\ViewEmployeeStatus;
use App\Filament\Resources\EmployeeStatuses\Schemas\EmployeeStatusForm;
use App\Filament\Resources\EmployeeStatuses\Schemas\EmployeeStatusInfolist;
use App\Filament\Resources\EmployeeStatuses\Tables\EmployeeStatusesTable;
use App\Models\EmployeeStatus;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class EmployeeStatusResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = EmployeeStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'statusName';
    protected static ?string $navigationLabel = 'حالات الموظفين';
    protected static string|UnitEnum|null $navigationGroup = 'ادارة الموظفين';

    protected static ?string $pluralModelLabel = 'حالات الموظفين';

    protected static ?string $modelLabel = 'حالة موظف';

    
    protected static function getResourcePermissionName(): string
    {
        return 'employee-statuses';
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeStatusForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeStatusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeStatusesTable::configure($table);
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
            'index' => ListEmployeeStatuses::route('/'),
            'create' => CreateEmployeeStatus::route('/create'),
            'view' => ViewEmployeeStatus::route('/{record}'),
            'edit' => EditEmployeeStatus::route('/{record}/edit'),
        ];
    }
}
