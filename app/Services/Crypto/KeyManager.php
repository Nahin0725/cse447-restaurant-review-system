<?php

namespace App\Services\Crypto;

use App\Models\KeyPair;
use App\Services\Crypto\Asymmetric\ECC;
use App\Services\Crypto\Asymmetric\RSA;
use Exception;

class KeyManager
{
    protected static $keysEnsured = false;

    public function __construct()
    {
        if (!self::$keysEnsured) {
            $this->ensureKeysExist();
            self::$keysEnsured = true;
        }
    }

    public function ensureKeysExist(): void
    {
        if (!KeyPair::where('status', 'active')->exists()) {
            ini_set('max_execution_time', 0);
            set_time_limit(0);
            \Log::warning('No active encryption keys found. Generating initial key material.');
            $this->rotateKeys();
        }
    }

    public function rotateKeys(): void
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        KeyPair::query()->update(['status' => 'rotated']);

        $rsaKeyPair = RSA::generateKeys(config('crypto.rsa_key_bits'));
        $eccKeyPair = ECC::generateKeyPair();

        KeyPair::create([
            'key_type' => 'rsa',
            'public_key' => json_encode(['n' => $rsaKeyPair['n'], 'e' => $rsaKeyPair['e']]),
            'private_key' => json_encode(['n' => $rsaKeyPair['n'], 'd' => $rsaKeyPair['d']]),
            'status' => 'active',
        ]);

        KeyPair::create([
            'key_type' => 'ecc',
            'public_key' => json_encode($eccKeyPair['public']),
            'private_key' => json_encode($eccKeyPair['private']),
            'status' => 'active',
        ]);
    }

    public function encryptUserField(string $plaintext): string
    {
        return RSA::encrypt($plaintext, $this->getActiveRsaPublic());
    }

    public function decryptUserField(string $ciphertext): string
    {
        $activePrivate = $this->getActiveRsaPrivate();

        try {
            $plaintext = RSA::decrypt($ciphertext, $activePrivate);

            if ($this->isLikelyPlaintext($plaintext)) {
                return $plaintext;
            }

            throw new \Exception('Active RSA key produced invalid plaintext.');
        } catch (\Exception $e) {
            \Log::warning('User field decryption failed with active RSA key. Trying inactive keys: ' . $e->getMessage());

            $inactiveKeys = KeyPair::where('key_type', 'rsa')
                ->where('status', 'rotated')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($inactiveKeys as $keyRecord) {
                try {
                    $privateKey = json_decode($keyRecord->private_key, true);

                    if (! is_array($privateKey)) {
                        continue;
                    }

                    $result = RSA::decrypt($ciphertext, $privateKey);

                    if ($this->isLikelyPlaintext($result)) {
                        \Log::warning('Successfully decrypted user field using inactive key ID ' . $keyRecord->id);
                        return $result;
                    }
                } catch (\Exception $keyError) {
                    continue;
                }
            }

            throw new \Exception('Could not decrypt user field with any available RSA key: ' . $e->getMessage());
        }
    }

    protected function isLikelyPlaintext(string $plaintext): bool
    {
        if ($plaintext === '') {
            return false;
        }

        if (! mb_check_encoding($plaintext, 'UTF-8')) {
            return false;
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $plaintext)) {
            return false;
        }

        $length = mb_strlen($plaintext, 'UTF-8');
        if ($length === 0) {
            return false;
        }

        preg_match_all('/[\p{L}\p{N}@._\-\s]/u', $plaintext, $matches);
        $allowedCount = count($matches[0]);

        return ($allowedCount / $length) >= 0.65;
    }

    public function encryptPostPayload(string $plaintext): string
    {
        return RSA::encrypt($plaintext, $this->getActiveRsaPublic());
    }

    public function decryptPostPayload(string $ciphertext): string
    {
        ini_set('max_execution_time', 300); // Increase timeout for decryption

        try {
            return RSA::decrypt($ciphertext, $this->getActiveRsaPrivate());
        } catch (\Exception $e) {
            \Log::warning('Post payload decryption failed with active RSA key. Trying inactive keys: ' . $e->getMessage());
            
            // Try inactive keys
            $inactiveKeys = KeyPair::where('key_type', 'rsa')
                ->where('status', 'rotated')
                ->orderBy('created_at', 'desc') // Try latest first
                ->limit(3) // Limit to 3 tries
                ->get();
            
            foreach ($inactiveKeys as $keyRecord) {
                try {
                    $decoded = json_decode($keyRecord->private_key, true);

                    if (! is_array($decoded)) {
                        continue;
                    }

                    $result = RSA::decrypt($ciphertext, $decoded);
                    \Log::warning('Successfully decrypted post payload using inactive key ID ' . $keyRecord->id);
                    return $result;
                } catch (\Exception $keyError) {
                    continue;
                }
            }
            
            // All keys failed
            throw new \Exception('Could not decrypt post payload with any available RSA key: ' . $e->getMessage());
        }
    }

    public function getTwoFactorEmail(): string
    {
        return config('crypto.two_factor_email');
    }

    protected function getActiveRsaPublic(): array
    {
        $decoded = json_decode($this->getActiveKeyPair('rsa')->public_key, true);

        if (! is_array($decoded)) {
            throw new Exception('Active RSA public key record is malformed.');
        }

        if (! RSA::isKeyLargeEnoughForOaep($decoded)) {
            \Log::warning('Active RSA public key is too small for OAEP padding. Rotating keys.');
            $this->rotateKeys();
            return $this->getActiveRsaPublic();
        }

        return $decoded;
    }

    protected function getActiveRsaPrivate(): array
    {
        $record = $this->getActiveKeyPair('rsa');
        
        if (!$record || empty($record->private_key)) {
            \Log::error('Active RSA private key record is empty. Rotating keys.');
            $this->rotateKeys();
            $record = $this->getActiveKeyPair('rsa');
        }

        $decoded = json_decode($record->private_key, true);

        if (! is_array($decoded) || ! isset($decoded['n'], $decoded['d'])) {
            \Log::error('Active RSA private key decoding failed. Record content: ' . substr($record->private_key, 0, 100) . '. Rotating keys.');
            $this->rotateKeys();

            $record = $this->getActiveKeyPair('rsa');
            $decoded = json_decode($record->private_key, true);

            if (! is_array($decoded) || ! isset($decoded['n'], $decoded['d'])) {
                throw new Exception('Unable to generate/retrieve valid RSA private key after key rotation.');
            }
        }

        if (! RSA::isKeyLargeEnoughForOaep($decoded)) {
            \Log::warning('Active RSA private key is too small for OAEP padding. Rotating keys.');
            $this->rotateKeys();
            return $this->getActiveRsaPrivate();
        }

        return $decoded;
    }

    public function getActiveEccPublic(): array
    {
        $decoded = json_decode($this->getActiveKeyPair('ecc')->public_key, true);

        if (! is_array($decoded)) {
            throw new Exception('Active ECC public key record is malformed.');
        }

        return $decoded;
    }

    public function getActiveEccPrivate(): string
    {
        $record = $this->getActiveKeyPair('ecc');
        
        if (!$record || empty($record->private_key)) {
            \Log::error('Active ECC private key record is empty. Rotating keys.');
            $this->rotateKeys();
            $record = $this->getActiveKeyPair('ecc');
        }

        $decoded = json_decode($record->private_key, true);

        if (! is_string($decoded) || ! preg_match('/^[0-9a-fA-F]+$/', $decoded)) {
            \Log::error('Active ECC private key decoding failed. Record content: ' . substr($record->private_key, 0, 100) . '. Rotating keys.');
            $this->rotateKeys();

            $record = $this->getActiveKeyPair('ecc');
            $decoded = json_decode($record->private_key, true);

            if (! is_string($decoded) || ! preg_match('/^[0-9a-fA-F]+$/', $decoded)) {
                throw new Exception('Unable to generate/retrieve valid ECC private key after key rotation.');
            }
        }

        return $decoded;
    }

    protected function getActiveKeyPair(string $keyType): KeyPair
    {
        $record = KeyPair::where('key_type', $keyType)
            ->where('status', 'active')
            ->first();

        if (! $record) {
            \Log::warning("No active {$keyType} key pair found. Generating new keys.");
            $this->rotateKeys();

            $record = KeyPair::where('key_type', $keyType)
                ->where('status', 'active')
                ->first();

            if (! $record) {
                throw new Exception("Failed to generate active {$keyType} key pair.");
            }
        }

        return $record;
    }

}
