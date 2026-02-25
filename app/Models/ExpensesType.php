<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpensesType extends Model
{
    protected $fillable = [
        'expenses_type_name',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expenses_type_id');
    }
}
