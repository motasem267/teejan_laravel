<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnualSubscriptionFee extends Model
{
    protected $fillable = [
        'grade_id',
        'academic_year_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(grade::class, 'grade_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(academic_years::class, 'academic_year_id');
    }
}
