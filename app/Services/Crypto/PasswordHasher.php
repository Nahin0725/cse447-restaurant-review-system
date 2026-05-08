<?php

namespace App\Services\Crypto;

class PasswordHasher
{
    public static function hash(string $password, string $salt = null): array
    {
        $salt ??= bin2hex(random_bytes(16));
        $hash = self::derive($password, $salt);

        return ['salt' => $salt, 'hash' => $hash];
    }

    public static function verify(string $password, string $salt, string $hash): bool
    {
        return hash_equals($hash, self::derive($password, $salt));
    }

    protected static function derive(string $password, string $salt): string
    {
        $result = hash('sha256', $salt . $password, true);

        for ($i = 0; $i < 12000; $i++) {
            $result = hash('sha256', $result . $salt . $password, true);
        }

        return bin2hex($result);
    }
}
