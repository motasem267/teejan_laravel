<?php

namespace App\Filament\Resources\GradeSubjects;

use App\Filament\Resources\GradeSubjects\Pages\CreateGradeSubject;
use App\Filament\Resources\GradeSubjects\Pages\EditGradeSubject;
use App\Filament\Resources\GradeSubjects\Pages\ListGradeSubjects;
use App\Filament\Resources\GradeSubjects\Pages\ViewGradeSubject;
use App\Filament\Resources\GradeSubjects\Schemas\GradeSubjectForm;
use App\Filament\Resources\GradeSubjects\Schemas\GradeSubjectInfolist;
use App\Filament\Resources\GradeSubjects\Tables\GradeSubjectsTable;
use App\Models\GradeSubject;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class GradeSubjectResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = GradeSubject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    protected static ?string $navigationLabel = 'تنسيب المواد';
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون الأكاديمية';
    
    protected static ?string $modelLabel = 'تنسيب مادة';
    
    protected static ?string $pluralModelLabel = 'تنسيب المواد';

    protected static function getResourcePermissionName(): string
    {
        return 'subjects';
    }

    public static function form(Schema $schema): Schema
    {
        return GradeSubjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GradeSubjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GradeSubjectsTable::configure($table);
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
            'index' => ListGradeSubjects::route('/'),
            'create' => CreateGradeSubject::route('/create'),
            'view' => ViewGradeSubject::route('/{record}'),
            'edit' => EditGradeSubject::route('/{record}/edit'),
        ];
    }
}
