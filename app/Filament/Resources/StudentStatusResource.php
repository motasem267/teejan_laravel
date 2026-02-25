<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentStatusResource\Pages;
use App\Models\StudentStatus;
use App\Traits\HasResourcePermissions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

class StudentStatusResource extends Resource
{
    use HasResourcePermissions;
    
    protected static ?string $model = StudentStatus::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'حالات الطلبة';

    protected static ?string $modelLabel = 'حالة طالب';

    protected static ?string $pluralModelLabel = 'حالات الطلبة';

    protected static string|UnitEnum|null $navigationGroup = 'إدارة الطلاب وأولياء الامور';

    protected static ?int $navigationSort = 3;

    protected static function getResourcePermissionName(): string
    {
        return 'student_status';
    }

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if (!method_exists($user, 'hasPermission')) {
            return false;
        }
        
        return $user->hasPermission('student_status.view');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('اسم الحالة')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('مثال: نشط، مُعلَّق، خارج، منقول')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('name')
                    ->label('اسم الحالة')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('students_count')
                    ->label('عدد الطلبة')
                    ->counts('students')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListStudentStatuses::route('/'),
            'create' => Pages\CreateStudentStatus::route('/create'),
            'edit' => Pages\EditStudentStatus::route('/{record}/edit'),
        ];
    }
}