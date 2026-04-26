<?php

namespace App\Filament\Resources\WorkDaysCalendars;

use App\Filament\Resources\WorkDaysCalendars\Pages\CreateWorkDaysCalendar;
use App\Filament\Resources\WorkDaysCalendars\Pages\EditWorkDaysCalendar;
use App\Filament\Resources\WorkDaysCalendars\Pages\ListWorkDaysCalendars;
use App\Filament\Resources\WorkDaysCalendars\Pages\ViewWorkDaysCalendar;
use App\Filament\Resources\WorkDaysCalendars\Schemas\WorkDaysCalendarForm;
use App\Filament\Resources\WorkDaysCalendars\Schemas\WorkDaysCalendarInfolist;
use App\Filament\Resources\WorkDaysCalendars\Tables\WorkDaysCalendarsTable;
use App\Models\WorkDaysCalendar;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class WorkDaysCalendarResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = WorkDaysCalendar::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'أيام الدوام';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون المالية';
    protected static ?string $modelLabel = 'أيام دوام';
    protected static ?string $pluralModelLabel = 'أيام الدوام';
    protected static ?string $recordTitleAttribute = 'WorkDaysCalendar';

    protected static function getResourcePermissionName(): string
    {
        return 'work-days-calendars';
    }

    public static function form(Schema $schema): Schema
    {
        return WorkDaysCalendarForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WorkDaysCalendarInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkDaysCalendarsTable::configure($table);
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
            'index' => ListWorkDaysCalendars::route('/'),
            'create' => CreateWorkDaysCalendar::route('/create'),
            'view' => ViewWorkDaysCalendar::route('/{record}'),
            'edit' => EditWorkDaysCalendar::route('/{record}/edit'),
        ];
    }
}
