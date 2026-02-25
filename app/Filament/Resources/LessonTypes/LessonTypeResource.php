<?php

namespace App\Filament\Resources\LessonTypes;

use App\Filament\Resources\LessonTypes\Pages\CreateLessonType;
use App\Filament\Resources\LessonTypes\Pages\EditLessonType;
use App\Filament\Resources\LessonTypes\Pages\ListLessonTypes;
use App\Filament\Resources\LessonTypes\Pages\ViewLessonType;
use App\Filament\Resources\LessonTypes\Schemas\LessonTypeForm;
use App\Filament\Resources\LessonTypes\Schemas\LessonTypeInfolist;
use App\Filament\Resources\LessonTypes\Tables\LessonTypesTable;
use App\Models\LessonType;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LessonTypeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = LessonType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'أنواع الحصص';
    protected static string|UnitEnum|null $navigationGroup = 'ادارة الجدول';
    protected static ?string $pluralModelLabel = 'أنواع الحصص';
    protected static ?string $modelLabel = 'نوع حصة';

    protected static function getResourcePermissionName(): string
    {
        return 'lesson_types';
    }

    public static function form(Schema $schema): Schema
    {
        return LessonTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LessonTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LessonTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLessonTypes::route('/'),
            'create' => CreateLessonType::route('/create'),
            'view' => ViewLessonType::route('/{record}'),
            'edit' => EditLessonType::route('/{record}/edit'),
        ];
    }
}
