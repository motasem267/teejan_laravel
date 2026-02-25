<?php

namespace App\Filament\Resources\SubjectFullMarks;

use App\Filament\Resources\SubjectFullMarks\Pages\CreateSubjectFullMark;
use App\Filament\Resources\SubjectFullMarks\Pages\EditSubjectFullMark;
use App\Filament\Resources\SubjectFullMarks\Pages\ListSubjectFullMarks;
use App\Filament\Resources\SubjectFullMarks\Pages\ViewSubjectFullMark;
use App\Filament\Resources\SubjectFullMarks\Schemas\SubjectFullMarkForm;
use App\Filament\Resources\SubjectFullMarks\Schemas\SubjectFullMarkInfolist;
use App\Filament\Resources\SubjectFullMarks\Tables\SubjectFullMarksTable;
use App\Models\SubjectFullMark;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class SubjectFullMarkResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = SubjectFullMark::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static string|UnitEnum|null $navigationGroup = 'الشؤون الأكاديمية';
    protected static ?string $navigationLabel = 'درجات المواد بالفترة';
    protected static ?string $modelLabel = 'درجة مادة';
    protected static ?string $pluralModelLabel = 'درجات المواد بالفترة';

    protected static function getResourcePermissionName(): string
    {
        return 'subjects';
    }

    public static function form(Schema $schema): Schema
    {
        return SubjectFullMarkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SubjectFullMarkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubjectFullMarksTable::configure($table);
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
            'index' => ListSubjectFullMarks::route('/'),
            'create' => CreateSubjectFullMark::route('/create'),
            'view' => ViewSubjectFullMark::route('/{record}'),
            'edit' => EditSubjectFullMark::route('/{record}/edit'),
        ];
    }
}
