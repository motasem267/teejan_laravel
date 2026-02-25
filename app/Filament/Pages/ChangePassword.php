<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use BackedEnum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'تغيير كلمة المرور';
    protected static string|UnitEnum|null $navigationGroup = 'ادارة الموظفين';
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-lock-closed';

    public ?array $data = [];
    public ?int $employeeId = null;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // If an employee_id is present in the query and it's not the current user,
        // require the permission to change other employees' passwords.
        $employeeId = request()->query('employee_id');
        if ($employeeId && intval($employeeId) !== $user->id) {
            if (! method_exists($user, 'hasPermission')) {
                return false;
            }

            return $user->hasPermission('employees.change-password');
        }

        // Otherwise allow authenticated users to change their own password.
        return true;
    }

    public function mount(): void
    {
        $this->employeeId = request()->query('employee_id') ? intval(request()->query('employee_id')) : null;
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $isChangingOther = $this->employeeId && auth()->user() && auth()->id() !== $this->employeeId;

        return $schema
            ->schema([
                TextInput::make('current_password')
                    ->label('كلمة المرور الحالية')
                    ->password()
                    ->required(fn () => ! $isChangingOther)
                    ->visible(fn () => ! $isChangingOther),

                TextInput::make('new_password')
                    ->label('كلمة المرور الجديدة')
                    ->password()
                    ->required()
                    ->minLength(8),

                TextInput::make('new_password_confirmation')
                    ->label('تأكيد كلمة المرور الجديدة')
                    ->password()
                    ->required()
                    ->same('new_password'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $currentUser = Auth::user();
        if (! $currentUser) {
            Notification::make()->title('خطأ')->body('المستخدم غير مسجل')->danger()->send();
            return;
        }

        // If an employeeId was provided and differs from current user, change that employee's password
        if ($this->employeeId && $this->employeeId !== $currentUser->id) {
            // ensure current user has permission
            if (! method_exists($currentUser, 'hasPermission') || ! $currentUser->hasPermission('employees.change-password')) {
                Notification::make()->title('خطأ')->body('لا تملك صلاحية تغيير كلمة مرور موظف آخر')->danger()->send();
                return;
            }

            $employee = \App\Models\Employee::find($this->employeeId);
            if (! $employee) {
                Notification::make()->title('خطأ')->body('الموظف غير موجود')->danger()->send();
                return;
            }

            $employee->password = $data['new_password'];
            $employee->save();

            $subjectModel = $employee;
        } else {
            // changing own password: require current password
            if (! Hash::check($data['current_password'] ?? '', $currentUser->password)) {
                Notification::make()->title('خطأ')->body('كلمة المرور الحالية غير صحيحة')->danger()->send();
                return;
            }

            $currentUser->password = $data['new_password'];
            $currentUser->save();

            $subjectModel = $currentUser;
        }

        // Log activity
        try {
            ActivityLog::create([
                'user_id' => $currentUser->id,
                'action' => 'change-password',
                'description' => ($currentUser->id === ($subjectModel->id ?? null) ? 'Changed own password' : 'Changed password for employee id ' . ($subjectModel->id ?? '')),
                'model_type' => get_class($subjectModel),
                'model_id' => $subjectModel->id ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // don't block on logging
        }

        Notification::make()
            ->title('تم')
            ->body('تم تغيير كلمة المرور بنجاح')
            ->success()
            ->send();

        $this->form->fill();
    }

    public function getTitle(): string
    {
        return 'تغيير كلمة المرور';
    }

    public function getView(): string
    {
        return 'filament.pages.change-password';
    }
}
