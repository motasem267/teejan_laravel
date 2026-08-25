<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Curriculum extends Model
{
    protected $table = 'curricula';

    protected $fillable = [
        'book_name',
        'grade_id',
        'subject_id',
        'file_path',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(grade::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(subject::class);
    }
}
