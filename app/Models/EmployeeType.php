<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeType extends Model
{
    protected $table = 'employee_types';
    
    protected $fillable = [
        'type_name',
    ];
    
    public $timestamps = true;

    /**
     * Get all employees of this type.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'emp_type_id');
    }
}
