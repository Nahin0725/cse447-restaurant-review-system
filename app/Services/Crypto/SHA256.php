<?php

namespace App\Services\Crypto;

use App\Services\Crypto\BigInteger;

class SHA256
{
    private static $k = [
        '428a2f98', '71374491', 'b5c0fbcf', 'e9b5dba5', '3956c25b', '59f111f1', '923f82a4', 'ab1c5ed5',
        'd807aa98', '12835b01', '243185be', '550c7dc3', '72be5d74', '80deb1fe', '9bdc06a7', 'c19bf174',
        'e49b69c1', 'efbe4786', '0fc19dc6', '240ca1cc', '2de92c6f', '4a7484aa', '5cb0a9dc', '76f988da',
        '983e5152', 'a831c66d', 'b00327c8', 'bf597fc7', 'c6e00bf3', 'd5a79147', '06ca6351', '14292967',
        '27b70a85', '2e1b2138', '4d2c6dfc', '53380d13', '650a7354', '766a0abb', '81c2c92e', '92722c85',
        'a2bfe8a1', 'a81a664b', 'c24b8b70', 'c76c51a3', 'd192e819', 'd6990624', 'f40e3585', '106aa070',
        '19a4c116', '1e376c08', '2748774c', '34b0bcb5', '391c0cb3', '4ed8aa4a', '5b9cca4f', '682e6ff3',
        '748f82ee', '78a5636f', '84c87814', '8cc70208', '90befffa', 'a4506ceb', 'bef9a3f7', 'c67178f2'
    ];

    private static $h = [
        '6a09e667', 'bb67ae85', '3c6ef372', 'a54ff53a', '510e527f', '9b05688c', '1f83d9ab', '5be0cd19'
    ];

    public static function hash(string $message): string
    {
        $message = self::preprocess($message);
        $chunks = str_split($message, 64); // 512 bits

        $h = array_map(fn($x) => BigInteger::fromHex($x), self::$h);

        foreach ($chunks as $chunk) {
            $w = self::schedule($chunk);
            $a = $h[0]; $b = $h[1]; $c = $h[2]; $d = $h[3];
            $e = $h[4]; $f = $h[5]; $g = $h[6]; $h_val = $h[7];

            for ($i = 0; $i < 64; $i++) {
                $s1 = BigInteger::bitwiseXor(BigInteger::bitwiseXor(self::rightRotate($e, 6), self::rightRotate($e, 11)), self::rightRotate($e, 25));
                $ch = BigInteger::bitwiseXor(BigInteger::bitwiseAnd($e, $f), BigInteger::bitwiseAnd(BigInteger::bitwiseNot($e), $g));
                $temp1 = self::toWord(BigInteger::add(BigInteger::add(BigInteger::add($h_val, $s1), $ch), BigInteger::add(BigInteger::fromHex(self::$k[$i]), $w[$i])));
                $s0 = BigInteger::bitwiseXor(BigInteger::bitwiseXor(self::rightRotate($a, 2), self::rightRotate($a, 13)), self::rightRotate($a, 22));
                $maj = BigInteger::bitwiseXor(BigInteger::bitwiseXor(BigInteger::bitwiseAnd($a, $b), BigInteger::bitwiseAnd($a, $c)), BigInteger::bitwiseAnd($b, $c));
                $temp2 = self::toWord(BigInteger::add($s0, $maj));

                $h_val = $g;
                $g = $f;
                $f = $e;
                $e = self::toWord(BigInteger::add($d, $temp1));
                $d = $c;
                $c = $b;
                $b = $a;
                $a = self::toWord(BigInteger::add($temp1, $temp2));
            }

            $h[0] = self::toWord(BigInteger::add($h[0], $a));
            $h[1] = self::toWord(BigInteger::add($h[1], $b));
            $h[2] = self::toWord(BigInteger::add($h[2], $c));
            $h[3] = self::toWord(BigInteger::add($h[3], $d));
            $h[4] = self::toWord(BigInteger::add($h[4], $e));
            $h[5] = self::toWord(BigInteger::add($h[5], $f));
            $h[6] = self::toWord(BigInteger::add($h[6], $g));
            $h[7] = self::toWord(BigInteger::add($h[7], $h_val));
        }

        $result = '';
        foreach ($h as $val) {
            $result .= str_pad(BigInteger::toHex($val), 8, '0', STR_PAD_LEFT);
        }

        return $result;
    }

    private static function preprocess(string $message): string
    {
        $len = strlen($message) * 8;
        $message .= chr(0x80);
        while ((strlen($message) % 64) !== 56) {
            $message .= chr(0x00);
        }
        $lenHex = str_pad(dechex($len), 16, '0', STR_PAD_LEFT);
        for ($i = 0; $i < 16; $i += 2) {
            $message .= chr(hexdec(substr($lenHex, $i, 2)));
        }
        return $message;
    }

    private static function schedule(string $chunk): array
    {
        $w = [];
        for ($i = 0; $i < 16; $i++) {
            $w[$i] = BigInteger::fromHex(bin2hex(substr($chunk, $i*4, 4)));
        }
        for ($i = 16; $i < 64; $i++) {
            $s0 = BigInteger::bitwiseXor(BigInteger::bitwiseXor(self::rightRotate($w[$i-15], 7), self::rightRotate($w[$i-15], 18)), self::rightShift($w[$i-15], 3));
            $s1 = BigInteger::bitwiseXor(BigInteger::bitwiseXor(self::rightRotate($w[$i-2], 17), self::rightRotate($w[$i-2], 19)), self::rightShift($w[$i-2], 10));
            $w[$i] = self::toWord(BigInteger::add(BigInteger::add(BigInteger::add($w[$i-16], $s0), $w[$i-7]), $s1));
        }
        return $w;
    }

    private static function toWord(mixed $value): mixed
    {
        return BigInteger::mod($value, BigInteger::pow2(32));
    }

    private static function rightRotate(mixed $val, int $bits): mixed
    {
        $hex = BigInteger::toHex($val);
        $hex = preg_replace('/[^0-9a-fA-F]/', '', $hex);
        if ($hex === '') {
            $hex = '0';
        }

        // Ensure even length and use only the lowest 32 bits for SHA-256 words.
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }

        if (strlen($hex) > 8) {
            $hex = substr($hex, -8);
        }

        $hex = str_pad($hex, 8, '0', STR_PAD_LEFT);
        $bin = hex2bin($hex);
        $rotated = substr($bin, -$bits) . substr($bin, 0, -$bits);
        return BigInteger::fromHex(bin2hex($rotated));
    }

    private static function rightShift(mixed $val, int $bits): mixed
    {
        return BigInteger::div($val, BigInteger::pow2($bits));
    }
}