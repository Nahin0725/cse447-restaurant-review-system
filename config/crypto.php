<?php

return [
    'root_secret' => env('CRYPTO_ROOT_SECRET', 'nahin-afrin-root-secret-2026'),
    'two_factor_email' => 'nahin.afrin@g.bracu.ac.bd',
    'session_hmac_salt' => env('CRYPTO_SESSION_SALT', 'resto-review-session-salt'),
    'rsa_key_bits' => 2048,
    'ecc_curve' => [
        'p' => '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F',
        'a' => '0x0',
        'b' => '0x7',
        'gx' => '0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798',
        'gy' => '0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8',
        'n' => '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141',
    ],
];
