<?php

namespace App\Filament\Resources\AcademicPeriods;

use App\Filament\Resources\AcademicPeriods\Pages\CreateAcademicPeriod;
use App\Filament\Resources\AcademicPeriods\Pages\EditAcademicPeriod;
use App\Filament\Resources\AcademicPeriods\Pages\ListAcademicPeriods;
use App\Filament\Resources\AcademicPeriods\Pages\ViewAcademicPeriod;
use App\Filament\Resources\AcademicPeriods\Schemas\AcademicPeriodForm;
use App\Filament\Resources\AcademicPeriods\Schemas\AcademicPeriodInfolist;
use App\Filament\Resources\AcademicPeriods\Tables\AcademicPeriodsTable;
use App\Models\AcademicPeriod;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AcademicPeriodResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = AcademicPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;
    protected static ?string $navigationLabel = 'الفترات الدراسية';
    protected static string|UnitEnum|null $navigationGroup = 'الفترات والتقويم الدراسي';
    
    protected static ?string $modelLabel = 'فترة دراسية';
    
    protected static ?string $pluralModelLabel = 'الفترات الدراسية';

    
    protected static function getResourcePermissionName(): string
    {
        return 'academic-periods';
    }

    public static function form(Schema $schema): Schema
    {
        return AcademicPeriodForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AcademicPeriodInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcademicPeriodsTable::configure($table);
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
            'index' => ListAcademicPeriods::route('/'),
            'create' => CreateAcademicPeriod::route('/create'),
            'view' => ViewAcademicPeriod::route('/{record}'),
            'edit' => EditAcademicPeriod::route('/{record}/edit'),
        ];
    }
}
