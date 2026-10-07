<?php

namespace App\Filament\Resources\EmployeeEnrollments;

use App\Filament\Resources\EmployeeEnrollments\Pages\CreateEmployeeEnrollment;
use App\Filament\Resources\EmployeeEnrollments\Pages\EditEmployeeEnrollment;
use App\Filament\Resources\EmployeeEnrollments\Pages\ListEmployeeEnrollments;
use App\Filament\Resources\EmployeeEnrollments\Schemas\EmployeeEnrollmentForm;
use App\Filament\Resources\EmployeeEnrollments\Tables\EmployeeEnrollmentsTable;
use App\Models\EmployeeEnrollment;
use App\Traits\HasResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeEnrollmentResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = EmployeeEnrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'قيد الموظفين';
    protected static string|UnitEnum|null $navigationGroup = 'ادارة الموظفين';
    protected static ?string $modelLabel = 'قيد موظف';
    protected static ?string $pluralModelLabel = 'قيد الموظفين';
    protected static ?int $navigationSort = 2;

    protected static function getResourcePermissionName(): string
    {
        return 'employee_enrollments';
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeEnrollmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeEnrollmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeEnrollments::route('/'),
            'create' => CreateEmployeeEnrollment::route('/create'),
            'edit' => EditEmployeeEnrollment::route('/{record}/edit'),
        ];
    }
}
