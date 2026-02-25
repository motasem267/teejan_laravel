<?php

namespace App\Filament\Resources\StudentEnrollments;

use App\Filament\Resources\StudentEnrollments\Pages\CreateStudentEnrollment;
use App\Filament\Resources\StudentEnrollments\Pages\EditStudentEnrollment;
use App\Filament\Resources\StudentEnrollments\Pages\ListStudentEnrollments;
use App\Filament\Resources\StudentEnrollments\Pages\ViewStudentEnrollment;
use App\Filament\Resources\StudentEnrollments\Schemas\StudentEnrollmentForm;
use App\Filament\Resources\StudentEnrollments\Schemas\StudentEnrollmentInfolist;
use App\Filament\Resources\StudentEnrollments\Tables\StudentEnrollmentsTable;
use App\Models\StudentEnrollment;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StudentEnrollmentResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = StudentEnrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'قيد الطلبة';
    protected static string|UnitEnum|null $navigationGroup = 'إدارة الطلاب وأولياء الامور';
    protected static ?string $modelLabel = 'قيد طالب';
    protected static ?string $pluralModelLabel = 'قيد الطلبة';
    protected static ?int $navigationSort = 2;

    protected static function getResourcePermissionName(): string
    {
        return 'student_enrollments';
    }

    public static function form(Schema $schema): Schema
    {
        return StudentEnrollmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentEnrollmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentEnrollmentsTable::configure($table);
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
            'index' => ListStudentEnrollments::route('/'),
            'create' => CreateStudentEnrollment::route('/create'),
            'view' => ViewStudentEnrollment::route('/{record}'),
            'edit' => EditStudentEnrollment::route('/{record}/edit'),
        ];
    }
}
