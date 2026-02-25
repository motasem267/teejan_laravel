<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'ip_address',
        'user_agent',
    ];
    
    public $timestamps = false;
    
    protected $dates = ['created_at'];

    /**
     * Get the employee (user) that performed the action.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }
    
    /**
     * Get the user that performed the action (alias for employee).
     */
    public function user(): BelongsTo
    {
        return $this->employee();
    }
}
