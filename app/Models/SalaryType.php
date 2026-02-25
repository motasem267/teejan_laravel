<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryType extends Model
{
    protected $table = 'salary_types';
    
    protected $fillable = [
        'name',
    ];
    
    public $timestamps = false;

    /**
     * Get all employees with this salary type.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'salary_by');
    }
}
