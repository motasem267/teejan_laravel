<?php

namespace App\Filament\Resources\Salaries\Pages;

use App\Filament\Resources\Salaries\SalaryResource;
use App\Models\Employee;
use App\Models\WorkDaysCalendar;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSalary extends CreateRecord
{
    protected static string $resource = SalaryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // التحقق من وجود أيام العمل في الجدول للموظف غير المعلم
        $employee = Employee::find($data['emp_id']);
        
        if ($employee && $employee->employeeType?->employee_type_name !== 'معلم') {
            $workDaysRecord = WorkDaysCalendar::where('month', $data['month'])
                ->where('year', $data['year'])
                ->first();
            
            if (!$workDaysRecord) {
                Notification::make()
                    ->danger()
                    ->title('خطأ في البيانات')
                    ->body('لا يمكن إضافة مرتب لهذا الشهر. يرجى إضافة عدد أيام الدوام للشهر أولاً في جدول أيام الدوام.')
                    ->persistent()
                    ->send();
                
                $this->halt();
            }
        }
        
        $data['created_by'] = auth()->id();
        
        return $data;
    }
}
