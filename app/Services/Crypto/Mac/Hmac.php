<?php

namespace App\Services\Crypto\Mac;

class Hmac
{
    public static function sign(string $message, string $key): string
    {
        $blockSize = 64;
        $key = strlen($key) > $blockSize ? hash('sha256', $key, true) : $key;
        $key = str_pad($key, $blockSize, "\0", STR_PAD_RIGHT);
        $oKeyPad = $key ^ str_repeat("\x5c", $blockSize);
        $iKeyPad = $key ^ str_repeat("\x36", $blockSize);

        return hash('sha256', $oKeyPad . hash('sha256', $iKeyPad . $message, true));
    }

    public static function verify(string $message, string $key, string $mac): bool
    {
        return hash_equals(self::sign($message, $key), $mac);
    }
}
