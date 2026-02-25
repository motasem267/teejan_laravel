<?php

namespace App\Filament\Resources\Days;

use App\Filament\Resources\Days\Pages\CreateDay;
use App\Filament\Resources\Days\Pages\EditDay;
use App\Filament\Resources\Days\Pages\ListDays;
use App\Filament\Resources\Days\Schemas\DayForm;
use App\Filament\Resources\Days\Tables\DayTable;
use App\Models\Day;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;


class DayResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = Day::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $recordTitleAttribute = 'day_name_ar';
    protected static ?string $navigationLabel = 'الأيام';
    protected static string|UnitEnum|null $navigationGroup = 'ادارة الجدول';

    protected static ?string $pluralModelLabel = 'الأيام';

    protected static ?string $modelLabel = 'يوم';

    protected static function getResourcePermissionName(): string
    {
        return 'days';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return DayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DayTable::configure($table);
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
            'index' => ListDays::route('/'),
            'create' => CreateDay::route('/create'),
            'edit' => EditDay::route('/{record}/edit'),
        ];
    }
}
