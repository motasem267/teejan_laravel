<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\academic_years;
use App\Models\EmployeeEnrollment;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    /**
     * الموظف الجديد يُقيد تلقائياً في السنة الدراسية الفعالة حتى يقدر يدخل للمنظومة.
     */
    protected function afterCreate(): void
    {
        if ($yearId = academic_years::getActiveId()) {
            EmployeeEnrollment::enroll($this->record, $yearId);
        }
    }
}
