<?php

namespace App\Services\Crypto;

use App\Services\Crypto\SHA256;

class HMAC
{
    public static function sign(string $message, string $key): string
    {
        $blockSize = 64; // 512 bits for SHA-256

        if (strlen($key) > $blockSize) {
            $key = hex2bin(SHA256::hash($key));
        }

        $key = str_pad($key, $blockSize, chr(0));

        $ipad = str_repeat(chr(0x36), $blockSize);
        $opad = str_repeat(chr(0x5c), $blockSize);

        $innerKey = $key ^ $ipad;
        $outerKey = $key ^ $opad;

        $innerHash = SHA256::hash($innerKey . $message);
        $outerHash = SHA256::hash($outerKey . hex2bin($innerHash));

        return $outerHash;
    }

    public static function verify(string $message, string $key, string $mac): bool
    {
        $computed = self::sign($message, $key);
        return hash_equals($computed, $mac);
    }
}