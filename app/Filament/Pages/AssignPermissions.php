<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\ActivityLog;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Filament\Resources\ActivityLogs\ActivityLogResource as ActivityLogResourceFilament;
use App\Filament\Pages\ChangePassword;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AssignPermissions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'إعطاء الصلاحيات';
    protected static string|UnitEnum|null $navigationGroup = 'إدارة المستخدمين والصلاحيات';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (!method_exists($user, 'hasPermission')) {
            return false;
        }

        return $user->hasPermission('assign-permissions.view');
    }

    public ?array $data = [];
    public ?int $selectedEmployeeId = null;

    public function mount(): void
    {
        try {
            Permission::firstOrCreate(
                ['name' => 'employees.change-password'],
                ['label' => 'تغيير كلمة المرور', 'parent_id' => null]
            );
        } catch (\Exception $e) {
        }

        $this->form->fill();
    }

    protected function getValidationRules(): array
    {
        $rules = [];

        $parentPermissions = Permission::whereNull('parent_id')->get();

        foreach ($parentPermissions as $parent) {
            $rules['permissions_' . $parent->id] = 'nullable|array';
            $rules['permissions_' . $parent->id . '.*'] = 'integer|exists:permissions,id';
        }

        $rules['permissions_monitoring_special'] = 'nullable|array';
        $rules['permissions_monitoring_special.*'] = 'integer|exists:permissions,id';

        return $rules;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                Section::make('اختيار الموظف')
                    ->schema([
                        Select::make('employee_id')
                            ->label('الموظف')
                            ->options(
                                Employee::whereNotNull('name')
                                    ->where('name', '!=', '')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->selectedEmployeeId = $state;
                                $this->loadEmployeePermissions();
                            }),
                    ]),

                Section::make('الصلاحيات')
                    ->schema(function () {

                        if (!$this->selectedEmployeeId) {
                            return [];
                        }

                        $components = [];

                        $permissionGroups = [
                            'إدارة الطلبة وأولياء الأمور' => ['students', 'parents', 'student_enrollments', 'student_status', 'grade-promotion'],
                            'إدارة الموظفين والمعلمين' => ['employees', 'employee-types', 'employee-statuses', 'teacher-classes'],
                            'الشؤون المالية' => ['installments', 'installment_types', 'annual_subscription_fees', 'expenses', 'expenses_types', 'bonuses', 'bonus_types', 'deductions', 'deduction_types', 'salaries', 'salary-types'],
                            'الإعدادات الأكاديمية' => ['grades', 'classes', 'sections', 'subjects', 'academic-years', 'academic-periods', 'academic-period-grades', 'week-results'],
                            'التقييمات والدرجات' => ['marks', 'evaluation_types', 'evaluation-questions', 'evaluation-answers', 'student-evaluation'],
                            'التقارير والمراقبة' => ['student-evaluations-report', 'reports', 'attendance-report', 'expenses-report', 'salaries-report', 'revenue-report'],
                            'الجدول الدراسي' => ['school-schedules', 'lesson-times', 'lesson_types', 'days', 'work-days-calendars', 'timetable-print'],
                            'النظام' => ['permissions', 'activity-logs', 'assign-permissions'],
                        ];

                        foreach ($permissionGroups as $groupName => $groupPermissionNames) {

                            $groupComponents = [];

                            $parentPermissions = Permission::whereNull('parent_id')
                                ->whereIn('name', $groupPermissionNames)
                                ->orderBy('name')
                                ->get();

                            foreach ($parentPermissions as $parent) {

                                $children = Permission::where('parent_id', $parent->id)
                                    ->orderBy('id')
                                    ->get();

                                // استثناء مراقبة نتائج الأسابيع من مجموعة الإعدادات الأكاديمية
                                if ($groupName === 'الإعدادات الأكاديمية' && $parent->name === 'week-results') {
                                    $children = $children->filter(fn ($child) => $child->name !== 'week-results.monitoring');
                                }

                                if ($children->isNotEmpty()) {
                                    $groupComponents[] = CheckboxList::make('permissions_' . $parent->id)
                                        ->label($parent->label)
                                        ->options($children->pluck('label', 'id')->toArray())
                                        ->columns(4)
                                        ->gridDirection('row')
                                        ->bulkToggleable()
                                        ->nullable();
                                }
                            }

                            // إضافة مراقبة نتائج الأسابيع في مجموعة التقارير والمراقبة
                            if ($groupName === 'التقارير والمراقبة') {

                                $weekResultsParent = Permission::whereNull('parent_id')
                                    ->where('name', 'week-results')
                                    ->first();

                                if ($weekResultsParent) {

                                    $monitoringPermission = Permission::where('parent_id', $weekResultsParent->id)
                                        ->where('name', 'week-results.monitoring')
                                        ->first();

                                    if ($monitoringPermission) {

                                        $groupComponents[] = CheckboxList::make('permissions_monitoring_special')
                                            ->label($monitoringPermission->label)
                                            ->options([
                                                $monitoringPermission->id => $monitoringPermission->label
                                            ])
                                            ->columns(4)
                                            ->gridDirection('row')
                                            ->bulkToggleable()
                                            ->nullable();
                                    }
                                }
                            }

                            if (!empty($groupComponents)) {
                                $components[] = Section::make($groupName)
                                    ->schema($groupComponents)
                                    ->collapsible()
                                    ->collapsed(false);
                            }
                        }

                        return $components;
                    })
                    ->visible(fn () => $this->selectedEmployeeId !== null),

            ])
            ->statePath('data');
    }

    /**
     * النسخة المصححة
     */
    protected function loadEmployeePermissions(): void
    {
        if (!$this->selectedEmployeeId) {
            return;
        }

        $employee = Employee::find($this->selectedEmployeeId);

        if (!$employee) {
            return;
        }

        $employeePermissions = $employee->permissions()
            ->pluck('id')
            ->toArray();

        $parentPermissions = Permission::whereNull('parent_id')
            ->orderBy('id')
            ->get();

        foreach ($parentPermissions as $parent) {

            $childIds = Permission::where('parent_id', $parent->id)
                ->pluck('id')
                ->toArray();

            // منع تعبئة صلاحية المراقبة داخل نتائج الأسابيع
            if ($parent->name === 'week-results') {

                $monitoringId = Permission::where('parent_id', $parent->id)
                    ->where('name', 'week-results.monitoring')
                    ->value('id');

                if ($monitoringId) {
                    $childIds = array_diff($childIds, [$monitoringId]);
                }
            }

            $selectedIds = array_intersect($childIds, $employeePermissions);

            $this->data['permissions_' . $parent->id] = array_values($selectedIds);
        }

        // تعبئة صلاحية المراقبة الخاصة
        $weekResultsParent = Permission::whereNull('parent_id')
            ->where('name', 'week-results')
            ->first();

        if ($weekResultsParent) {

            $monitoringPermission = Permission::where('parent_id', $weekResultsParent->id)
                ->where('name', 'week-results.monitoring')
                ->first();

            if ($monitoringPermission) {

                $this->data['permissions_monitoring_special'] =
                    in_array($monitoringPermission->id, $employeePermissions)
                        ? [$monitoringPermission->id]
                        : [];
            }
        }

        $this->form->fill($this->data);
    }

    public function save(): void
    {
        $this->form->getState();

        if (!$this->selectedEmployeeId) {
            Notification::make()
                ->title('خطأ')
                ->body('يرجى اختيار موظف')
                ->danger()
                ->send();
            return;
        }

        try {

            $employee = Employee::find($this->selectedEmployeeId);

            if (!$employee) {
                Notification::make()
                    ->title('خطأ')
                    ->body('الموظف غير موجود')
                    ->danger()
                    ->send();
                return;
            }

            $allPermissions = [];

            foreach ($this->data as $key => $value) {
                if (str_starts_with($key, 'permissions_') && is_array($value)) {
                    $allPermissions = array_merge($allPermissions, $value);
                }
            }

            $current = $employee->permissions()->pluck('id')->toArray();

            $employee->permissions()->sync($allPermissions);

            $added   = array_values(array_diff($allPermissions, $current));
            $removed = array_values(array_diff($current, $allPermissions));

            try {
                ActivityLog::create([
                    'user_id'    => Auth::id(),
                    'action'     => 'assign-permissions',
                    'description'=> 'Updated permissions for employee id ' . $employee->id .
                        '. Added: ' . implode(',', $added) .
                        '; Removed: ' . implode(',', $removed),
                    'model_type' => Employee::class,
                    'model_id'   => $employee->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } catch (\Exception $e) {
            }

            $this->loadEmployeePermissions();

            Notification::make()
                ->title('تم الحفظ بنجاح')
                ->body('تم تحديث صلاحيات الموظف بنجاح')
                ->success()
                ->send();

        } catch (\Exception $e) {

            Notification::make()
                ->title('خطأ')
                ->body('حدث خطأ أثناء حفظ الصلاحيات: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getView(): string
    {
        return 'filament.pages.assign-permissions';
    }

    public function getTitle(): string
    {
        return 'إعطاء الصلاحيات';
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('change_password')
                ->label('تغيير كلمة المرور')
                ->url(function () {

                    if (!$this->selectedEmployeeId) {
                        return ChangePassword::getUrl();
                    }

                    return ChangePassword::getUrl() . '?employee_id=' . $this->selectedEmployeeId;
                })
                ->visible(fn () =>
                    $this->selectedEmployeeId !== null &&
                    (
                        Auth::id() === $this->selectedEmployeeId ||
                        (
                            Auth::user()
                            && method_exists(Auth::user(), 'hasPermission')
                            && Auth::user()->hasPermission('employees.change-password')
                        )
                    )
                ),

            Action::make('activity_logs')
                ->label('سجل الأنشطة')
                ->url(function () {

                    if (!$this->selectedEmployeeId) {
                        return ActivityLogResourceFilament::getUrl('index');
                    }

                    return ActivityLogResourceFilament::getUrl('index')
                        . '?model_type=' . urlencode(\App\Models\Employee::class)
                        . '&model_id=' . $this->selectedEmployeeId;
                })
                ->visible(fn () =>
                    $this->selectedEmployeeId !== null
                    && Auth::user()
                    && method_exists(Auth::user(), 'hasPermission')
                    && Auth::user()->hasPermission('activity-logs.view')
                ),

        ];
    }
}
