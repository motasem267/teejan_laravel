<?php

namespace App\Filament\Actions;

use App\Models\academic_years;
use App\Models\Employee;
use App\Models\student;
use App\Services\IdCardPdfService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * أزرار إصدار بطاقات التعريف المستخدمة في جداول الطلبة والموظفين.
 */
class IdCardActions
{
    public const PERMISSION_STUDENTS = 'id-cards.students';
    public const PERMISSION_EMPLOYEES = 'id-cards.employees';

    public static function canIssueStudentCards(): bool
    {
        return static::userHas(self::PERMISSION_STUDENTS);
    }

    public static function canIssueEmployeeCards(): bool
    {
        return static::userHas(self::PERMISSION_EMPLOYEES);
    }

    public static function studentRecord(): Action
    {
        return Action::make('studentIdCard')
            ->label('بطاقة تعريف')
            ->icon('heroicon-o-identification')
            ->color('warning')
            ->visible(fn () => static::canIssueStudentCards())
            ->modalHeading('إصدار بطاقة تعريف طالب')
            ->modalSubmitActionLabel('إصدار البطاقة')
            ->schema(static::studentOptionsSchema())
            ->action(fn (student $record, array $data) => static::downloadStudents(collect([$record]), $data));
    }

    public static function studentsBulk(): BulkAction
    {
        return BulkAction::make('studentsIdCards')
            ->label('إصدار بطاقات تعريف')
            ->icon('heroicon-o-identification')
            ->color('warning')
            ->visible(fn () => static::canIssueStudentCards())
            ->modalHeading('إصدار بطاقات تعريف للطلبة المحددين')
            ->modalSubmitActionLabel('إصدار البطاقات')
            ->schema(static::studentOptionsSchema())
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records, array $data) => static::downloadStudents($records, $data));
    }

    public static function employeeRecord(): Action
    {
        return Action::make('employeeIdCard')
            ->label('بطاقة تعريف')
            ->icon('heroicon-o-identification')
            ->color('warning')
            ->visible(fn () => static::canIssueEmployeeCards())
            ->modalHeading('إصدار بطاقة تعريف موظف')
            ->modalSubmitActionLabel('إصدار البطاقة')
            ->schema([static::layoutField()])
            ->action(fn (Employee $record, array $data) => static::downloadEmployees(collect([$record]), $data));
    }

    public static function employeesBulk(): BulkAction
    {
        return BulkAction::make('employeesIdCards')
            ->label('إصدار بطاقات تعريف')
            ->icon('heroicon-o-identification')
            ->color('warning')
            ->visible(fn () => static::canIssueEmployeeCards())
            ->modalHeading('إصدار بطاقات تعريف للموظفين المحددين')
            ->modalSubmitActionLabel('إصدار البطاقات')
            ->schema([static::layoutField()])
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records, array $data) => static::downloadEmployees($records, $data));
    }

    public static function layoutField(): Radio
    {
        return Radio::make('layout')
            ->label('طريقة الطباعة')
            ->options(IdCardPdfService::layoutOptions())
            ->default(IdCardPdfService::LAYOUT_A4)
            ->required();
    }

    public static function academicYearField(): Select
    {
        return Select::make('academic_year_id')
            ->label('السنة الدراسية')
            ->helperText('يُطبع الصف والشعبة حسب قيد الطالب في هذه السنة')
            ->options(fn () => academic_years::orderByDesc('id')->pluck('year_label', 'id'))
            ->default(fn () => academic_years::getActiveId())
            ->required();
    }

    public static function downloadStudents(Collection $students, array $data): ?StreamedResponse
    {
        abort_unless(static::canIssueStudentCards(), 403);

        if ($students->isEmpty()) {
            Notification::make()->warning()->title('لا يوجد طلبة لإصدار بطاقات لهم')->send();

            return null;
        }

        $pdf = app(IdCardPdfService::class)->students(
            $students,
            isset($data['academic_year_id']) ? (int) $data['academic_year_id'] : null,
            $data['layout'] ?? IdCardPdfService::LAYOUT_A4,
        );

        return static::download($pdf, IdCardPdfService::filename(IdCardPdfService::TYPE_STUDENT, $students->count()), $students->count());
    }

    public static function downloadEmployees(Collection $employees, array $data): ?StreamedResponse
    {
        abort_unless(static::canIssueEmployeeCards(), 403);

        if ($employees->isEmpty()) {
            Notification::make()->warning()->title('لا يوجد موظفون لإصدار بطاقات لهم')->send();

            return null;
        }

        $pdf = app(IdCardPdfService::class)->employees(
            $employees,
            $data['layout'] ?? IdCardPdfService::LAYOUT_A4,
        );

        return static::download($pdf, IdCardPdfService::filename(IdCardPdfService::TYPE_EMPLOYEE, $employees->count()), $employees->count());
    }

    private static function studentOptionsSchema(): array
    {
        return [
            static::academicYearField(),
            static::layoutField(),
        ];
    }

    private static function download(string $content, string $filename, int $count): StreamedResponse
    {
        Notification::make()
            ->success()
            ->title('تم إصدار ' . $count . ' بطاقة')
            ->send();

        return response()->streamDownload(
            fn () => print($content),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    private static function userHas(string $permission): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permission);
    }
}
