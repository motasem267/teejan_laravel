<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeStatus extends Model
{
    protected $table = 'employee_statuses';
    
    protected $fillable = [
        'status_name',
    ];
    
    public $timestamps = true;

    /**
     * Get all employees with this status.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'status_id');
    }
}
