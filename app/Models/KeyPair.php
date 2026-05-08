<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeyPair extends Model
{
    public $timestamps = false;
    protected $table = 'key_pairs';
    protected $primaryKey = 'key_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'key_type',
        'public_key',
        'private_key',
        'created_at',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && now()->greaterThan($this->expiry_date);
    }
}
