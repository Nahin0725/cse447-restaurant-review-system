<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacRecord extends Model
{
    protected $table = 'mac_records';
    protected $primaryKey = 'mac_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'reference_id',
        'mac_value',
        'algorithm',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
