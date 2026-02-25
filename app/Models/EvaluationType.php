<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationType extends Model
{
    protected $table = 'evaluation_types';
    
    protected $fillable = [
        'code',
        'label',
    ];
    
    public $timestamps = false;

    /**
     * Get all questions for this evaluation type.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class, 'evaluation_type_id');
    }

    /**
     * Get all evaluations of this type.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluation_type_id');
    }
}
