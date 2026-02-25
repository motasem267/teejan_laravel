<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherClass extends Model
{
    protected $table = 'teacher_classes';
    
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'section_id',
        'academic_year_id',
    ];
    
    public $timestamps = false;

    /**
     * Get the teacher (employee).
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }

    /**
     * Get the subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(subject::class);
    }

    /**
     * Get the class (grade + section).
     */
    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get all school schedules for this teacher class.
     */
    public function schoolSchedules(): HasMany
    {
        return $this->hasMany(SchoolSchedule::class, 'teacher_class_id');
    }

     // السنة الدراسية
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(academic_years::class);
    }
}