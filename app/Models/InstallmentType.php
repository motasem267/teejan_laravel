<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentType extends Model
{
    protected $fillable = [
        'installment_type_name',
    ];

    public function installments()
    {
        return $this->hasMany(Installment::class, 'installment_type_id');
    }
}
