<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEvaluationRecord extends Model
{
    protected $table = 'student_evaluations';

    protected $fillable = [
        'student_id',
        'question_id',
        'answer_id',
        'month',
        'week',
        'academic_year_id',
        'teacher_id',
    ];

    public $timestamps = true;

    const UPDATED_AT = 'updated_at';

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(student::class, 'student_id');
    }

    /**
     * Get the question.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(EvaluationQuestion::class, 'question_id');
    }

    /**
     * Get the answer.
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(EvaluationAnswer::class, 'answer_id');
    }

    /**
     * Get the academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(academic_years::class, 'academic_year_id');
    }

    /**
     * Get the teacher (evaluator).
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }
}
