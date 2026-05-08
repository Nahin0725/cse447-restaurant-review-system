<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $table = 'otps';
    protected $primaryKey = 'otp_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'otp_code',
        'generated_at',
        'expiry_time',
        'is_used',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'expiry_time' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expiry_time);
    }
}
