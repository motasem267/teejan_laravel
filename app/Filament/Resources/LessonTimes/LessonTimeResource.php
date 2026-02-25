<?php

namespace App\Filament\Resources\LessonTimes;

use App\Filament\Resources\LessonTimes\Pages\CreateLessonTime;
use App\Filament\Resources\LessonTimes\Pages\EditLessonTime;
use App\Filament\Resources\LessonTimes\Pages\ListLessonTimes;
use App\Filament\Resources\LessonTimes\Schemas\LessonTimeForm;
use App\Filament\Resources\LessonTimes\Tables\LessonTimeTable;
use App\Models\LessonTime;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;


class LessonTimeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = LessonTime::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'period_number';
    protected static ?string $navigationLabel = 'أوقات الحصص';
    protected static string|UnitEnum|null $navigationGroup = 'ادارة الجدول';
    protected static ?string $pluralModelLabel = 'أوقات الحصص';
    protected static ?string $modelLabel = 'وقت حصة';

    protected static function getResourcePermissionName(): string
    {
        return 'lesson-times';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return LessonTimeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LessonTimeTable::configure($table);
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
            'index' => ListLessonTimes::route('/'),
            'create' => CreateLessonTime::route('/create'),
            'edit' => EditLessonTime::route('/{record}/edit'),
        ];
    }
}
