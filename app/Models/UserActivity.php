<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'user_activities';
    protected $primaryKey = 'activity_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'remaining_reviews',
        'last_post_time',
        'cooldown_end_time',
    ];

    protected $casts = [
        'last_post_time' => 'datetime',
        'cooldown_end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function canPostReview(): bool
    {
        // Check if in cooldown
        if ($this->cooldown_end_time && now()->lessThan($this->cooldown_end_time)) {
            return false;
        }

        return $this->remaining_reviews > 0;
    }

    public function getTimeUntilNextReview(): ?\DateTime
    {
        if ($this->cooldown_end_time && now()->lessThan($this->cooldown_end_time)) {
            return $this->cooldown_end_time;
        }

        return null;
    }
}
