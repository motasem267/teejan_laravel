<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionType extends Model
{
    protected $fillable = [
        'deduction_type_name',
    ];

    public function deductions()
    {
        return $this->hasMany(Deduction::class, 'deduction_type_id');
    }
}
