<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class student extends Model
{
    protected $fillable = [
        'id',
        'national_id',
        'full_name',
        'parent_id',
        'mother_phone',
        'status_id',
    ];
    
    public $incrementing = true;
    
    public $timestamps = true;
    
    /**
     * Get the parent of the student.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    /**
     * Get the status of the student.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(StudentStatus::class, 'status_id');
    }

    /**
     * Get the installment type (for forms only, not stored).
     */
    public function installmentType(): BelongsTo
    {
        return $this->belongsTo(InstallmentType::class);
    }

    /**
     * Get the payment method (for forms only, not stored).
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get all marks for the student.
     */
    public function marks(): HasMany
    {
        return $this->hasMany(mark::class);
    }

    /**
     * الحصول على جميع قيود الطالب في الصفوف والسنوات الدراسية.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}
