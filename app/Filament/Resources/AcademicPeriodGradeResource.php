<?php
namespace App\Filament\Resources;

use App\Filament\Resources\AcademicPeriodGradeResource\Pages;
use App\Models\AcademicPeriodGrade;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AcademicPeriodGradeResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = AcademicPeriodGrade::class;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;
    protected static ?string $navigationLabel = 'اعلان النتائج';
    protected static string|UnitEnum|null $navigationGroup = 'التقييم والدرجات';
    protected static ?string $modelLabel = 'اعلان نتائج';
    protected static ?string $pluralModelLabel = 'اعلان النتائج';
    protected static ?int $navigationSort = 5;

    protected static function getResourcePermissionName(): string
    {
        return 'academic-period-grades';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('AcademicPeriodID')
                    ->label('الفترة الدراسية')
                    ->relationship('academicPeriod', 'PeriodName')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                    
                Select::make('GradeID')
                    ->label('الصف الدراسي')
                    ->relationship('grade', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                    
                Toggle::make('IsViewed')
                    ->label('مسموح بالرؤية')
                    ->default(false)
                    ->helperText('فعّل لجعل نتائج هذا الصف مرئية في هذه الفترة الدراسية'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable(),
                    
                TextColumn::make('academicPeriod.PeriodName')
                    ->label('الفترة الدراسية')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('grade.name')
                    ->label('الصف الدراسي')
                    ->searchable()
                    ->sortable(),
                    
                IconColumn::make('IsViewed')
                    ->label('مسموح بالرؤية')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicPeriodGrades::route('/'),
            'create' => Pages\CreateAcademicPeriodGrade::route('/create'),
            'edit' => Pages\EditAcademicPeriodGrade::route('/{record}/edit'),
        ];
    }
}
