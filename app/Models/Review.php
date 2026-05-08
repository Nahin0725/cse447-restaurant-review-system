<?php

namespace App\Models;

use App\Services\Crypto\KeyManager;
use App\Services\Crypto\HMAC;
use App\Services\Crypto\Asymmetric\ECC;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'restaurant_name',
        'review_text',
        'review_score',
        'location',
        'city',
        'status',
        'edit_count',
        'max_edit_limit',
        'signature',
        'mac',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'signature' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function postedReviews()
    {
        return $this->hasMany(PostedReview::class, 'review_id', 'review_id');
    }

    public function adminActions()
    {
        return $this->hasMany(AdminAction::class, 'review_id', 'review_id');
    }

    // Encryption/Decryption for review_text
    public function setReviewTextAttribute(string $value): void
    {
        $this->attributes['review_text'] = app(KeyManager::class)->encryptUserField($value);
    }

    public function getReviewTextAttribute(): string
    {
        $encrypted = $this->getRawOriginal('review_text');
        
        if (empty($encrypted)) {
            return '';
        }

        try {
            return app(KeyManager::class)->decryptUserField($encrypted);
        } catch (\Exception $e) {
            \Log::warning('Review text decryption failed: ' . $e->getMessage());
            return '';
        }
    }

    public function verifyIntegrity(): bool
    {// Verify MAC
        if (!empty($this->mac)) {
            $message = json_encode([
                'review_id' => $this->review_id,
                'user_id' => $this->user_id,
                'restaurant_name' => $this->restaurant_name,
                'review_text' => $this->review_text,
                'review_score' => $this->review_score,
                'city' => $this->city,
            ]);
            if (!HMAC::verify($message, config('crypto.root_secret'), $this->mac)) {
                return false;
            }
        }

        // Verify ECC signature
        if (!empty($this->signature)) {
            $message = json_encode([
                'review_id' => $this->review_id,
                'user_id' => $this->user_id,
                'restaurant_name' => $this->restaurant_name,
                'review_text' => $this->review_text,
                'review_score' => $this->review_score,
                'city' => $this->city,
            ]);

            $signature = $this->signature;
            if (is_string($signature)) {
                $signature = json_decode($signature, true) ?: [];
            }

            if (!is_array($signature) || !isset($signature['r'], $signature['s'])) {
                return false;
            }

            $publicKey = app(KeyManager::class)->getActiveEccPublic();
            return ECC::verify($message, $signature, $publicKey);
        }

        return true;
    }

    public function generateMac(): void
    {
        $message = json_encode([
            'review_id' => $this->review_id,
            'user_id' => $this->user_id,
            'restaurant_name' => $this->restaurant_name,
            'review_text' => $this->review_text,
            'review_score' => $this->review_score,
            'city' => $this->city,
        ]);

        $this->mac = HMAC::sign($message, config('crypto.root_secret'));
    }

    public function generateSignature(): void
    {
        $message = json_encode([
            'review_id' => $this->review_id,
            'user_id' => $this->user_id,
            'restaurant_name' => $this->restaurant_name,
            'review_text' => $this->review_text,
            'review_score' => $this->review_score,
            'city' => $this->city,
        ]);

        $privateKey = app(KeyManager::class)->getActiveEccPrivate();

        if (!$privateKey) {
            throw new \Exception('ECC private key not available for signing.');
        }

        $this->signature = ECC::sign($message, $privateKey);
    }
}
