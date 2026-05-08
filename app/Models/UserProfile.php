<?php

namespace App\Models;

use App\Services\Crypto\KeyManager;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    protected $primaryKey = 'profile_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'profile_data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function setProfileDataAttribute(string $value): void
    {
        $this->attributes['profile_data'] = app(KeyManager::class)->encryptUserField($value);
    }

    public function getProfileDataAttribute(): string
    {
        $encrypted = $this->getRawOriginal('profile_data');
        
        if (empty($encrypted)) {
            return '';
        }

        try {
            return app(KeyManager::class)->decryptUserField($encrypted);
        } catch (\Exception $e) {
            \Log::warning('Profile data decryption failed: ' . $e->getMessage());
            return '';
        }
    }
}
