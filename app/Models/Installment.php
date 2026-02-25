<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    protected $fillable = [
        'parent_id',
        'installment_type_id',
        'amount',
        'description',
        'payment_type_id',
        'academic_year',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function installmentType(): BelongsTo
    {
        return $this->belongsTo(InstallmentType::class, 'installment_type_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
