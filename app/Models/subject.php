<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class subject extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'full_mark',
    ];

    /**
     * Get all marks for the subject.
     */
    public function marks(): HasMany
    {
        return $this->hasMany(mark::class);
    }

    /**
     * Get all grades that have this subject.
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(grade::class, 'grades_subject', 'subjectID', 'gradeID');
    }

    /**
     * Get all grade-subject assignments.
     */
    public function gradeSubjects(): HasMany
    {
        return $this->hasMany(GradeSubject::class, 'subjectID');
    }
}
