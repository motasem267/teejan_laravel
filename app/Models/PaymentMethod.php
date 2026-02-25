<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    protected $fillable = [
        'payment_type',
    ];

    public function installments()
    {
        return $this->hasMany(Installment::class, 'payment_type_id');
    }
}
