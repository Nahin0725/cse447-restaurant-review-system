<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAction extends Model
{
    protected $table = 'admin_actions';
    protected $primaryKey = 'action_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'review_id',
        'status',
        'action_time',
    ];

    protected $casts = [
        'action_time' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id', 'review_id');
    }
}
