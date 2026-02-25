<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeSubject extends Model
{
    public $timestamps = false;
    
    protected $table = 'grades_subject';
    
    protected $fillable = [
        'subjectID',
        'gradeID',
    ];

    /**
     * Get the subject that belongs to the grade.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(subject::class, 'subjectID');
    }

    /**
     * Get the grade.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(grade::class, 'gradeID');
    }
}
