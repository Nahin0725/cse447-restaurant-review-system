<?php

namespace App\Models;

use App\Services\Crypto\KeyManager;
use App\Services\Crypto\PasswordHasher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'username',
        'email',
        'contact_info',
        'password',
        'password_hash',
        'password_salt',
        'role',
    ];

    protected $hidden = [
        'password_hash',
        'password_salt',
        'email_hash',
        'contact_hash',
    ];

    // Relationships
    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }

    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id');
    }

    public function otps()
    {
        return $this->hasMany(Otp::class, 'user_id', 'user_id');
    }

    public function userActivity()
    {
        return $this->hasOne(UserActivity::class, 'user_id', 'user_id');
    }

    public function sessions()
    {
        return $this->hasMany(SessionTable::class, 'user_id', 'user_id');
    }

    // Username Attribute Accessors
    public function setUsernameAttribute(string $value): void
    {
        $this->attributes['username_hash'] = hash('sha256', $value);
        $this->attributes['username'] = app(KeyManager::class)->encryptUserField($value);
    }

    public function getUsernameAttribute(): string
    {
        $encrypted = $this->getRawOriginal('username');
        
        if (empty($encrypted)) {
            return '';
        }

        try {
            $plaintext = app(KeyManager::class)->decryptUserField($encrypted);
            return $plaintext ?? '';
        } catch (\Exception $e) {
            \Log::warning('User username decryption failed: ' . $e->getMessage());
            return '';
        }
    }

    // Email Attribute Accessors
    public function setEmailAttribute(string $value): void
    {
        $normalized = strtolower(trim($value));
        $this->attributes['email_hash'] = hash('sha256', $normalized);
        $this->attributes['email'] = app(KeyManager::class)->encryptUserField($normalized);
    }

    public function getEmailAttribute(): string
    {
        $encrypted = $this->getRawOriginal('email');
        
        if (empty($encrypted)) {
            return '';
        }

        try {
            return app(KeyManager::class)->decryptUserField($encrypted);
        } catch (\Exception $e) {
            \Log::warning('User email decryption failed: ' . $e->getMessage());
            return '';
        }
    }

    // Contact Info Attribute Accessors
    public function setContactInfoAttribute(string $value): void
    {
        $normalized = self::normalizeContact($value);
        $this->attributes['contact_hash'] = hash('sha256', $normalized);
        $this->attributes['contact_info'] = app(KeyManager::class)->encryptUserField($value);
    }

    public function getContactInfoAttribute(): string
    {
        $encrypted = $this->getRawOriginal('contact_info');
        
        if (empty($encrypted)) {
            return '';
        }

        try {
            return app(KeyManager::class)->decryptUserField($encrypted);
        } catch (\Exception $e) {
            \Log::warning('User contact decryption failed: ' . $e->getMessage());
            return '';
        }
    }

    // Helper Methods
    private static function normalizeContact(string $value): string
    {
        return preg_replace('/\D+/', '', $value);
    }

    public static function findByContact(string $contact): ?self
    {
        return self::where('contact_hash', hash('sha256', self::normalizeContact($contact)))->first();
    }

    public static function findByEmail(string $email): ?self
    {
        return self::where('email_hash', hash('sha256', strtolower(trim($email))))->first();
    }

    public static function findByUsername(string $username): ?self
    {
        return self::where('username_hash', hash('sha256', $username))->first();
    }

    public function setPasswordAttribute(string $value): void
    {
        $result = PasswordHasher::hash($value);
        $this->attributes['password_salt'] = $result['salt'];
        $this->attributes['password_hash'] = $result['hash'];
    }

    public function verifyPassword(string $password): bool
    {
        return PasswordHasher::verify($password, $this->password_salt, $this->password_hash);
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
