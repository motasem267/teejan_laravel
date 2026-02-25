<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentModel extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    protected static function booted(): void
    {
        // عند حذف ولي الأمر: فكّ ربط الأبناء به فقط دون حذفهم
        static::deleting(function (ParentModel $parent) {
            $parent->students()->update(['parent_id' => null]);
        });
    }

    public function students(): HasMany
    {
        return $this->hasMany(student::class, 'parent_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'parent_id');
    }
}
