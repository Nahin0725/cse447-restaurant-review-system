<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLimit extends Model
{
    protected $table = 'admin_limits';
    protected $primaryKey = 'admin_limit_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'admin_id',
        'current_pending_count',
        'max_pending_limit',
        'status',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    public function canApprovMore(): bool
    {
        return $this->current_pending_count < $this->max_pending_limit && $this->status === 'active';
    }

    public function isWaiting(): bool
    {
        return $this->status === 'wait';
    }
}
