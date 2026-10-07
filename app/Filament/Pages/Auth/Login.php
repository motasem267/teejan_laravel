<?php

namespace App\Filament\Pages\Auth;

use App\Models\Employee;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Validation\ValidationException;

/**
 * صفحة الدخول: نفس صفحة Filament، مع رسالة واضحة للموظف غير المقيد في السنة الدراسية الفعالة
 * بدل رسالة "بيانات الدخول غير صحيحة".
 */
class Login extends BaseLogin
{
    protected function throwFailureValidationException(): never
    {
        if ($this->failedOnlyBecauseNotEnrolled()) {
            throw ValidationException::withMessages([
                'data.email' => 'حسابك غير مقيد في السنة الدراسية الحالية. تواصل مع الإدارة لإضافتك لقيد الموظفين.',
            ]);
        }

        parent::throwFailureValidationException();
    }

    private function failedOnlyBecauseNotEnrolled(): bool
    {
        try {
            $provider = Filament::auth()->getProvider();
            $credentials = $this->getCredentialsFromFormData($this->form->getState());
            $user = $provider->retrieveByCredentials($credentials);

            return $user instanceof Employee
                && $provider->validateCredentials($user, $credentials)
                && ! $user->isAllowedForActiveYear();
        } catch (\Throwable) {
            return false;
        }
    }
}
