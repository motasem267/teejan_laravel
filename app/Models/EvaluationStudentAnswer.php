<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationStudentAnswer extends Model
{
    protected $table = 'evaluation_students_answers';
    
    protected $fillable = [
        'evaluation_id',
        'question_id',
        'answer_id',
    ];

    public $timestamps = true;

    /**
     * Get the evaluation.
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
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
}
