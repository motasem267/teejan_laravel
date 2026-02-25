<?php

namespace App\Filament\Resources\Marks;

use App\Filament\Resources\Marks\Pages\CreateMark;
use App\Filament\Resources\Marks\Pages\EditMark;
use App\Filament\Resources\Marks\Pages\ListMarks;
use App\Filament\Resources\Marks\Pages\ViewMark;
use App\Filament\Resources\Marks\Schemas\MarkForm;
use App\Filament\Resources\Marks\Schemas\MarkInfolist;
use App\Filament\Resources\Marks\Tables\MarksTable;
use App\Models\mark;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use UnitEnum;

class MarkResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = mark::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;
    protected static ?string $navigationLabel = 'الدرجات';
    protected static string|UnitEnum|null $navigationGroup = 'التقييم والدرجات';
    protected static ?string $modelLabel = 'درجة';
    protected static ?string $pluralModelLabel = 'الدرجات';

    
    protected static function getResourcePermissionName(): string
    {
        return 'marks';
    }

    public static function form(Schema $schema): Schema
    {
        return MarkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MarkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarksTable::configure($table);
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
            'index' => ListMarks::route('/'),
            'create' => CreateMark::route('/create'),
            'view' => ViewMark::route('/{record}'),
            'edit' => EditMark::route('/{record}/edit'),
        ];
    }
}
