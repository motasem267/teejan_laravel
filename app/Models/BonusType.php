<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusType extends Model
{
    protected $fillable = [
        'bonus_type_name',
    ];

    public function bonuses()
    {
        return $this->hasMany(Bonus::class, 'bonus_type_id');
    }
}
