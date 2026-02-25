<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class grade extends Model
{

    // اسماء الحقول المسموح بالكتابة الجماعية
    protected $fillable = [
        'name',
        // إذا عندك حقول أخرى مثل 'score' أضفها هنا
    ];

    public $timestamps = false;

    /**
     * Get all students for the grade.
     */
    public function students(): HasMany
    {
        return $this->hasMany(student::class);
    }

    /**
     * Get all subjects assigned to this grade.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(subject::class, 'grades_subject', 'gradeID', 'subjectID');
    }

    /**
     * Get all grade-subject assignments.
     */
    public function gradeSubjects(): HasMany
    {
        return $this->hasMany(GradeSubject::class, 'gradeID');
    }

    /**
     * Get all class models for this grade.
     */
    public function classModels(): HasMany
    {
        return $this->hasMany(ClassModel::class, 'grade_id');
    }
}
