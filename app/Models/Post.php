<?php

namespace App\Models;

use App\Models\User;
use App\Services\Crypto\KeyManager;
use App\Services\Crypto\HMAC;
use App\Services\Crypto\Asymmetric\ECC;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_name',
        'title',
        'body',
        'review_score',
        'city',
        'status',
        'signature',
        'mac',
    ];

    protected $casts = [
        'signature' => 'array',
    ];

    protected $hidden = [
        'restaurant_name_encrypted',
        'title_encrypted',
        'body_encrypted',
        'city_encrypted',
        'hmac',
    ];

    protected static function booted()
    {
        static::saving(function (Post $post) {
            $manager = app(KeyManager::class);
            if (! empty($post->restaurant_name_encrypted) && ! empty($post->title_encrypted) && ! empty($post->body_encrypted)) {
                $message = ($post->restaurant_name_encrypted ?? '') . '::' .
                    $post->title_encrypted . '::' .
                    $post->city_encrypted . '::' .
                    $post->body_encrypted . '::' .
                    ($post->review_score ?? 0);
// Generate MAC
                $post->mac = HMAC::sign($message, config('crypto.root_secret'));

                // Generate ECC signature
                $privateKey = $manager->getActiveEccPrivate();

                if ($privateKey) {
                    $post->signature = ECC::sign($message, $privateKey);
                }
            }
        });
    }

    public function getTitleAttribute(): string
    {
        return $this->decryptAttribute('title_encrypted');
    }

    public function setTitleAttribute(string $value): void
    {
        $this->attributes['title_encrypted'] = app(KeyManager::class)->encryptPostPayload($value);
    }

    public function getCityAttribute(): string
    {
        return $this->decryptAttribute('city_encrypted');
    }

    public function setCityAttribute(string $value): void
    {
        $this->attributes['city_encrypted'] = app(KeyManager::class)->encryptPostPayload($value);
    }

    public function getBodyAttribute(): string
    {
        return $this->decryptAttribute('body_encrypted');
    }

    public function setBodyAttribute(string $value): void
    {
        $this->attributes['body_encrypted'] = app(KeyManager::class)->encryptPostPayload($value);
    }

    public function getRestaurantNameAttribute(): string
    {
        $encrypted = $this->getRawOriginal('restaurant_name_encrypted');
        
        if (empty($encrypted)) {
            return '';
        }

        try {
            return app(KeyManager::class)->decryptPostPayload($encrypted);
        } catch (\Exception $e) {
            \Log::warning('Post restaurant_name decryption failed for post ' . $this->id . ': ' . $e->getMessage());
            return '';
        }
    }

    public function setRestaurantNameAttribute(string $value): void
    {
        $this->attributes['restaurant_name_encrypted'] = app(KeyManager::class)->encryptPostPayload($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifyIntegrity(): bool
    {
        $message = ($this->restaurant_name_encrypted ?? '') . '::' .
            $this->title_encrypted . '::' .
            $this->city_encrypted . '::' .
            $this->body_encrypted . '::' .
            ($this->review_score ?? 0);

        // Verify MAC
        if (!empty($this->mac) && !HMAC::verify($message, config('crypto.root_secret'), $this->mac)) {
            return false;
        }

        // Verify ECC signature
        if (!empty($this->signature)) {
            $publicKey = app(KeyManager::class)->getActiveEccPublic();
            return ECC::verify($message, $this->signature, $publicKey);
        }

        return true;
    }

    protected function decryptAttribute(string $attribute): string
    {
        $encrypted = $this->getRawOriginal($attribute);
        
        if (empty($encrypted)) {
            return '';
        }

        try {
            return app(KeyManager::class)->decryptPostPayload($encrypted);
        } catch (\Exception $e) {
            \Log::warning('Post payload decryption failed for post ' . $this->id . ': ' . $e->getMessage());
            return '';
        }
    }
}
