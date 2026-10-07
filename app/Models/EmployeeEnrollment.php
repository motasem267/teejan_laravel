<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * قيد الموظف في سنة دراسية (الموظف + الوظيفة في تلك السنة).
 */
class EmployeeEnrollment extends Model
{
    protected $fillable = [
        'employee_id',
        'academic_year_id',
        'emp_type_id',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(academic_years::class, 'academic_year_id');
    }

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class, 'emp_type_id');
    }

    /**
     * قيد موظف في سنة (يتجاهل الموجود مسبقاً). يعيد true إذا أُنشئ قيد جديد.
     */
    public static function enroll(Employee $employee, int $academicYearId, ?int $empTypeId = null): bool
    {
        $enrollment = static::firstOrCreate(
            ['employee_id' => $employee->id, 'academic_year_id' => $academicYearId],
            ['emp_type_id' => $empTypeId ?? $employee->emp_type_id],
        );

        return $enrollment->wasRecentlyCreated;
    }
}
