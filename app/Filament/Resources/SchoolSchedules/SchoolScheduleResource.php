<?php

namespace App\Filament\Resources\SchoolSchedules;

use App\Filament\Resources\SchoolSchedules\Pages\CreateSchoolSchedule;
use App\Filament\Resources\SchoolSchedules\Pages\EditSchoolSchedule;
use App\Filament\Resources\SchoolSchedules\Pages\ListSchoolSchedules;
use App\Filament\Resources\SchoolSchedules\Schemas\SchoolScheduleForm;
use App\Filament\Resources\SchoolSchedules\Tables\SchoolScheduleTable;
use App\Models\SchoolSchedule;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;


class SchoolScheduleResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = SchoolSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'الجدول الدراسي';
    protected static string|UnitEnum|null $navigationGroup = 'ادارة الجدول';
    protected static ?string $pluralModelLabel = 'الجداول الدراسية';
    protected static ?string $modelLabel = 'جدول دراسي';

    protected static function getResourcePermissionName(): string
    {
        return 'school-schedules';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return SchoolScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchoolScheduleTable::configure($table);
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
            'index' => ListSchoolSchedules::route('/'),
            'create' => CreateSchoolSchedule::route('/create'),
            'edit' => EditSchoolSchedule::route('/{record}/edit'),
        ];
    }
}
