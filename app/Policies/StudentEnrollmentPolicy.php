<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\StudentEnrollment;
use Illuminate\Auth\Access\Response;

class StudentEnrollmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Employee $employee): bool
    {
        return $employee->hasPermission('student_enrollments.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Employee $employee, StudentEnrollment $studentEnrollment): bool
    {
        return $employee->hasPermission('student_enrollments.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Employee $employee): bool
    {
        return $employee->hasPermission('student_enrollments.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Employee $employee, StudentEnrollment $studentEnrollment): bool
    {
        return $employee->hasPermission('student_enrollments.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Employee $employee, StudentEnrollment $studentEnrollment): bool
    {
        return $employee->hasPermission('student_enrollments.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Employee $employee, StudentEnrollment $studentEnrollment): bool
    {
        return $employee->hasPermission('student_enrollments.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Employee $employee, StudentEnrollment $studentEnrollment): bool
    {
        return $employee->hasPermission('student_enrollments.delete');
    }
}
