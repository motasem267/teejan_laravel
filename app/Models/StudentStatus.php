<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentStatus extends Model
{
    protected $table = 'student_status';
    
    protected $fillable = [
        'name',
    ];
    
    public $timestamps = false;

    /**
     * Get all students with this status.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'status_id');
    }
}