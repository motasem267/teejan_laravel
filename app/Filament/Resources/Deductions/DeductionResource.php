<?php

namespace App\Filament\Resources\Deductions;

use App\Filament\Resources\Deductions\Pages\CreateDeduction;
use App\Filament\Resources\Deductions\Pages\EditDeduction;
use App\Filament\Resources\Deductions\Pages\ListDeductions;
use App\Filament\Resources\Deductions\Pages\ViewDeduction;
use App\Filament\Resources\Deductions\Schemas\DeductionForm;
use App\Filament\Resources\Deductions\Schemas\DeductionInfolist;
use App\Filament\Resources\Deductions\Tables\DeductionsTable;
use App\Models\Deduction;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class DeductionResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Deduction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMinusCircle;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'الخصومات';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';
    protected static ?string $pluralModelLabel = 'الخصومات';
    protected static ?string $modelLabel = 'خصم';

    protected static function getResourcePermissionName(): string
    {
        return 'deductions';
    }

    public static function form(Schema $schema): Schema
    {
        return DeductionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DeductionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeductionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeductions::route('/'),
            'create' => CreateDeduction::route('/create'),
            'view' => ViewDeduction::route('/{record}'),
            'edit' => EditDeduction::route('/{record}/edit'),
        ];
    }
}
