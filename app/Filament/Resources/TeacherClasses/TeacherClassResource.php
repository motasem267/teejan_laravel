<?php

namespace App\Filament\Resources\TeacherClasses;

use App\Filament\Resources\TeacherClasses\Pages\CreateTeacherClass;
use App\Filament\Resources\TeacherClasses\Pages\EditTeacherClass;
use App\Filament\Resources\TeacherClasses\Pages\ListTeacherClasses;
use App\Filament\Resources\TeacherClasses\Pages\ViewTeacherClass;
use App\Filament\Resources\TeacherClasses\Schemas\TeacherClassForm;
use App\Filament\Resources\TeacherClasses\Schemas\TeacherClassInfolist;
use App\Filament\Resources\TeacherClasses\Tables\TeacherClassesTable;
use App\Models\TeacherClass;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class TeacherClassResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = TeacherClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'معلمي الفصول';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون الأكاديمية';
    protected static ?string $pluralModelLabel = 'معلمي الفصول';
    protected static ?string $modelLabel = 'معلم فصل';

    
    protected static function getResourcePermissionName(): string
    {
        return 'teacher-classes';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return TeacherClassForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeacherClassInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeacherClassesTable::configure($table);
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
            'index' => ListTeacherClasses::route('/'),
            'create' => CreateTeacherClass::route('/create'),
            'view' => ViewTeacherClass::route('/{record}'),
            'edit' => EditTeacherClass::route('/{record}/edit'),
        ];
    }
}
