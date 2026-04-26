<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser; // <--- إضافة 1 use Filament\Panel;
use Filament\Panel; // <--- إضافة 2


class Employee extends Authenticatable implements FilamentUser
{
    use Notifiable;
    
    protected $table = 'employees';
    
    protected $fillable = [
        'id',
        'name',
        'emp_type_id',
        'status_id',
        'salary',
        'password',
        'salary_by',
        'phone_number',
        'email',
    ];
    
    public $incrementing = false;
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'password' => 'hashed',
    ];
    
    public $timestamps = true;
    
    /**
     * Get the name of the unique identifier for the user.
     */

   public function canAccessPanel(Panel $panel): bool     
    {
       return true; 
    }


    public function getAuthIdentifierName(): string
    {
        return 'id';
    }
    
    /**
     * Get the name of the password field for authentication.
     */
    public function getAuthPasswordName(): string
    {
        return 'password';
    }
    
    /**
     * Get the column name for the "username" column.
     */
    public function findForAuth(string $username): ?self
    {
        return static::where('email', $username)->first();
    }
    
    /**
     * Get the name attribute for Filament display.
     */
    public function getNameAttribute($value)
    {
        return $value ?? $this->email;
    }

    /**
     * Get the employee type.
     */
    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class, 'emp_type_id');
    }

    /**
     * Get the employee status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(EmployeeStatus::class, 'status_id');
    }

    /**
     * Get the salary type.
     */
    public function salaryType(): BelongsTo
    {
        return $this->belongsTo(SalaryType::class, 'salary_by');
    }

    /**
     * Get the permissions for the employee.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'employee_permissions', 'employee_id', 'permission_id');
    }

    /**
     * Get all teacher classes for this employee.
     */
    public function teacherClasses(): HasMany
    {
        return $this->hasMany(TeacherClass::class, 'teacher_id');
    }

    /**
     * Get all evaluations done by this employee.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    /**
     * Get all student evaluation records by this teacher.
     */
    public function evaluationRecords(): HasMany
    {
        return $this->hasMany(StudentEvaluationRecord::class, 'teacher_id');
    }

    /**
     * Get all activity logs for this employee.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'employee_id');
    }

    /**
     * Get all daily class attendance snapshots for this employee.
     */
    public function dailyClassAttendances(): HasMany
    {
        return $this->hasMany(DailyClassAttendance::class, 'employee_id');
    }

    /**
     * Check if employee has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        // Use relationship cache if loaded, otherwise query
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->contains('name', $permissionName);
        }
        
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Check if employee can view a resource.
     */
    public function canView(string $resource): bool
    {
        return $this->hasPermission($resource . '.view');
    }

    /**
     * Check if employee can create a resource.
     */
    public function canCreate(string $resource): bool
    {
        return $this->hasPermission($resource . '.create');
    }

    /**
     * Check if employee can edit a resource.
     */
    public function canEdit(string $resource): bool
    {
        return $this->hasPermission($resource . '.edit');
    }

    /**
     * Check if employee can delete a resource.
     */
    public function canDelete(string $resource): bool
    {
        return $this->hasPermission($resource . '.delete');
    }
}
