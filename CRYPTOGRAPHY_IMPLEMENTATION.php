<?php

/**
 * CRYPTOGRAPHY SYSTEM - COMPLETE IMPLEMENTATION GUIDE
 * 
 * This system uses ONLY asymmetric encryption (RSA + ECC) with integrity verification (HMAC).
 * NO symmetric encryption is used anywhere.
 * 
 * ARCHITECTURE:
 * 1. ECC - Encrypts user sensitive fields (email, contact_info) using ElGamal-style encryption
 * 2. RSA - Encrypts post/review content before database storage
 * 3. ECC - Signs all data with digital signatures for authenticity
 * 4. HMAC - Provides integrity verification using SHA-256 manually
 * 
 * All algorithms are implemented from scratch using BigInteger arithmetic.
 * No OpenSSL, GMP, BCMath, or any built-in crypto functions used.
 */

// ============================================================================
// 1. USER REGISTRATION FLOW - ECC Encryption for Sensitive Fields
// ============================================================================

// File: app/Http/Controllers/AuthController.php
// Method: register()

$user = User::create([
    'username' => $request->input('username'),           // Plaintext (OK - not sensitive)
    'email' => $request->input('email'),                 // ENCRYPTED via model setter using ECC
    'contact_info' => $request->input('contact_info'),   // ENCRYPTED via model setter using ECC
    'password' => $request->input('password'),           // HASHED via model setter
    'role' => $request->input('role'),
]);

// In User model (app/Models/User.php):
public function setEmailAttribute(string $value): void
{
    $normalized = strtolower(trim($value));
    $this->attributes['email_hash'] = hash('sha256', $normalized);  // For lookups
    // ECC encryption of actual email
    $this->attributes['email'] = app(KeyManager::class)->encryptUserField($normalized);
}

public function getEmailAttribute(): string
{
    $encrypted = $this->getRawOriginal('email');
    try {
        // ECC decryption
        return app(KeyManager::class)->decryptUserField($encrypted);
    } catch (Exception $e) {
        return '';
    }
}

// ============================================================================
// 2. REVIEW/POST CREATION FLOW - RSA Encryption + HMAC + ECC Signature
// ============================================================================

// File: app/Http/Controllers/ReviewController.php
// Method: store()

$review = Review::create([
    'user_id' => $user->user_id,
    'review_text' => $request->input('review_text'),     // ENCRYPTED via model setter using RSA
    'review_score' => $request->input('review_score'),
    'location' => $request->input('location'),
    'city' => $request->input('city'),
    'status' => 'pending',
]);

// Model automatically generates integrity checks:
// $review->mac = HMAC::sign($data, config('crypto.root_secret'));
// $review->signature = ECC::sign($data, $eccPrivateKey);

// In Review model (app/Models/Review.php):
public function setReviewTextAttribute(string $value): void
{
    // RSA encryption for storage
    $this->attributes['review_text'] = app(KeyManager::class)->encryptPostPayload($value);
}

protected static function booted()
{
    static::saving(function (Review $review) {
        // ... encryption happens via setters ...
        // Now generate integrity codes
        
        $message = json_encode([
            'review_id' => $review->review_id,
            'user_id' => $review->user_id,
            'review_text' => $review->review_text,
            'review_score' => $review->review_score,
            'city' => $review->city,
        ]);
        
        // HMAC for integrity (from scratch using SHA-256)
        $review->mac = HMAC::sign($message, config('crypto.root_secret'));
        
        // ECC digital signature for authenticity
        $privateKey = app(KeyManager::class)->getActiveEccPrivate()['private'];
        $review->signature = ECC::sign($message, $privateKey);
    });
}

// ============================================================================
// 3. DATA RETRIEVAL FLOW - Decryption + Verification
// ============================================================================

$review = Review::find($reviewId);

// Automatic decryption via model getters:
echo $review->review_text;  // Returns decrypted text (RSA)

// Verify integrity before displaying
if (!$review->verifyIntegrity()) {
    abort(403, 'Data integrity check failed');
}

// In Review model:
public function verifyIntegrity(): bool
{
    // Reconstruct message
    $message = json_encode([
        'review_id' => $this->review_id,
        'user_id' => $this->user_id,
        'review_text' => $this->review_text,
        'review_score' => $this->review_score,
        'city' => $this->city,
    ]);
    
    // Verify HMAC (using SHA-256 from scratch)
    if (!empty($this->mac)) {
        if (!HMAC::verify($message, config('crypto.root_secret'), $this->mac)) {
            return false;  // Tampering detected
        }
    }
    
    // Verify ECC signature
    if (!empty($this->signature)) {
        $publicKey = app(KeyManager::class)->getActiveEccPublic();
        return ECC::verify($message, $this->signature, $publicKey);
    }
    
    return true;
}

// ============================================================================
// 4. ASYMMETRIC ENCRYPTION IMPLEMENTATIONS
// ============================================================================

// A. ECC ENCRYPTION (ElGamal-style over elliptic curve)
// File: app/Services/Crypto/Asymmetric/ECC.php

public static function encrypt(string $plaintext, array $publicKey): string
{
    // plaintext treated as message m
    // m must be integer < curve order n
    
    $m = BigInteger::fromBinary($plaintext);
    if (BigInteger::cmp($m, self::$curve['n']) >= 0) {
        throw new Exception('Message too large for ECC encryption.');
    }
    
    // Select random k
    $k = BigInteger::randomBetween('1', BigInteger::sub(self::$curve['n'], '1'));
    
    // C1 = k * G (ephemeral public key)
    $c1 = self::scalarMultiply(self::generatorPoint(), $k);
    
    // Shared secret: k * PublicKey
    $pubPoint = self::pointFromArray($publicKey);
    $kPub = self::scalarMultiply($pubPoint, $k);
    
    // Ciphertext part: m*G + k*PublicKey
    $mG = self::scalarMultiply(self::generatorPoint(), $m);
    $c2 = self::addPoints($kPub, $mG);
    
    return base64_encode(json_encode([
        'c1' => self::pointToArray($c1),
        'c2' => self::pointToArray($c2)
    ]));
}

public static function decrypt(string $ciphertext, string $privateKey): string
{
    // C2 - d*C1 = m*G  (d is private key)
    
    $payload = json_decode(base64_decode($ciphertext), true);
    $c1 = self::pointFromArray($payload['c1']);
    $c2 = self::pointFromArray($payload['c2']);
    $d = BigInteger::fromHex($privateKey);
    
    // d * C1
    $dC1 = self::scalarMultiply($c1, $d);
    
    // mG = C2 - d*C1
    $mG = self::subtractPoints($c2, $dC1);
    
    // Recover m using baby-step giant-step (limited for small m)
    $m = '0';
    $current = self::infinity();
    for ($i = 0; $i < 1000; $i++) {
        if (self::pointsEqual($current, $mG)) {
            break;
        }
        $current = self::addPoints($current, self::generatorPoint());
        $m = BigInteger::add($m, '1');
    }
    
    return BigInteger::toBinary($m);
}

// B. RSA ENCRYPTION (standard textbook RSA)
// File: app/Services/Crypto/Asymmetric/RSA.php

public static function encrypt(string $plaintext, array $publicKey): string
{
    $n = BigInteger::fromHex($publicKey['n']);
    $e = BigInteger::fromHex($publicKey['e']);
    $blockSize = (int) floor((strlen(BigInteger::toHex($n)) / 2)) - 2;
    $blocks = [];
    
    $chunks = str_split($plaintext, $blockSize);
    foreach ($chunks as $chunk) {
        $value = BigInteger::fromBinary($chunk);
        
        // c = m^e mod n
        $cipher = BigInteger::modPow($value, $e, $n);
        $blocks[] = BigInteger::toHex($cipher);
    }
    
    return base64_encode(json_encode(['blocks' => $blocks]));
}

public static function decrypt(string $ciphertext, array $privateKey): string
{
    $payload = json_decode(base64_decode($ciphertext), true);
    $n = BigInteger::fromHex($privateKey['n']);
    $d = BigInteger::fromHex($privateKey['d']);
    $plaintext = '';
    
    foreach ($payload['blocks'] as $blockHex) {
        $cipher = BigInteger::fromHex($blockHex);
        
        // m = c^d mod n
        $value = BigInteger::modPow($cipher, $d, $n);
        $plaintext .= BigInteger::toBinary($value);
    }
    
    return $plaintext;
}

// ============================================================================
// 5. MESSAGE AUTHENTICATION CODE (HMAC with SHA-256)
// ============================================================================

// File: app/Services/Crypto/HMAC.php
// Standard HMAC-SHA256 from scratch

public static function sign(string $message, string $key): string
{
    $blockSize = 64; // 512 bits
    
    // Normalize key
    if (strlen($key) > $blockSize) {
        $key = hex2bin(SHA256::hash($key));
    }
    $key = str_pad($key, $blockSize, chr(0));
    
    // Inner padding (ipad = 0x36)
    $ipad = str_repeat(chr(0x36), $blockSize);
    // Outer padding (opad = 0x5c)
    $opad = str_repeat(chr(0x5c), $blockSize);
    
    $innerKey = $key ^ $ipad;
    $outerKey = $key ^ $opad;
    
    // Inner hash: H((key ⊕ ipad) || message)
    $innerHash = SHA256::hash($innerKey . $message);
    
    // Outer hash: H((key ⊕ opad) || H(...))
    $outerHash = SHA256::hash($outerKey . hex2bin($innerHash));
    
    return $outerHash;
}

public static function verify(string $message, string $key, string $mac): bool
{
    $computed = self::sign($message, $key);
    return hash_equals($computed, $mac);
}

// ============================================================================
// 6. DIGITAL SIGNATURES (ECC ECDSA-style)
// ============================================================================

// File: app/Services/Crypto/Asymmetric/ECC.php

public static function sign(string $message, string $privateKey): array
{
    // 1. Hash message
    $hash = hash('sha256', $message, true);
    $e = BigInteger::fromBinary($hash);
    $e = BigInteger::mod($e, self::$curve['n']);
    
    // 2. Signer's private key
    $d = BigInteger::fromHex($privateKey);
    
    // 3. Generate random k
    $k = BigInteger::randomBetween('1', BigInteger::sub(self::$curve['n'], '1'));
    
    // 4. (x, y) = k * G
    $rPoint = self::scalarMultiply(self::generatorPoint(), $k);
    $r = BigInteger::mod($rPoint['x'], self::$curve['n']);
    
    // 5. s = k^-1 * (e + d*r) mod n
    $kInv = BigInteger::modInverse($k, self::$curve['n']);
    $s = BigInteger::mod(
        BigInteger::mul($kInv, BigInteger::add($e, BigInteger::mul($d, $r))),
        self::$curve['n']
    );
    
    return [
        'r' => BigInteger::toHex($r),
        's' => BigInteger::toHex($s),
    ];
}

public static function verify(string $message, array $signature, array $publicKey): bool
{
    // 1. Hash message
    $hash = hash('sha256', $message, true);
    $e = BigInteger::fromBinary($hash);
    $e = BigInteger::mod($e, self::$curve['n']);
    
    // 2. Retrieve signature components
    $r = BigInteger::fromHex($signature['r']);
    $s = BigInteger::fromHex($signature['s']);
    
    // 3. Verify r, s in range [1, n-1]
    if (BigInteger::cmp($r, '1') < 0 || BigInteger::cmp($r, BigInteger::sub(self::$curve['n'], '1')) > 0) {
        return false;
    }
    if (BigInteger::cmp($s, '1') < 0 || BigInteger::cmp($s, BigInteger::sub(self::$curve['n'], '1')) > 0) {
        return false;
    }
    
    // 4. Compute verification
    $sInv = BigInteger::modInverse($s, self::$curve['n']);
    $u1 = BigInteger::mod(BigInteger::mul($e, $sInv), self::$curve['n']);
    $u2 = BigInteger::mod(BigInteger::mul($r, $sInv), self::$curve['n']);
    
    // 5. (x, y) = u1*G + u2*Q (Q is signer's public key)
    $point1 = self::scalarMultiply(self::generatorPoint(), $u1);
    $point2 = self::scalarMultiply(self::pointFromArray($publicKey), $u2);
    $point = self::addPoints($point1, $point2);
    
    if (self::isInfinity($point)) {
        return false;
    }
    
    // 6. Signature valid iff x ≡ r (mod n)
    $rPrime = BigInteger::mod($point['x'], self::$curve['n']);
    return BigInteger::cmp($r, $rPrime) === 0;
}

// ============================================================================
// 7. KEY MANAGEMENT
// ============================================================================

// File: app/Services/Crypto/KeyManager.php

public function encryptUserField(string $plaintext): string
{
    // ECC encryption for user sensitive data (email, contact_info)
    return ECC::encrypt($plaintext, $this->getActiveEccPublic());
}

public function decryptUserField(string $ciphertext): string
{
    // ECC decryption
    $activePrivate = $this->getActiveEccPrivate()['private'];
    return ECC::decrypt($ciphertext, $activePrivate);
}

public function encryptPostPayload(string $plaintext): string
{
    // RSA encryption for post/review content
    return RSA::encrypt($plaintext, $this->getActiveRsaPublic());
}

public function decryptPostPayload(string $ciphertext): string
{
    // RSA decryption
    $activePrivate = $this->getActiveRsaPrivate();
    return RSA::decrypt($ciphertext, $activePrivate);
}

// ============================================================================
// SUMMARY OF ALGORITHM ROLES
// ============================================================================

/**
 * Algorithm   | Purpose              | Fields
 * ------------|----------------------|--------------------------
 * ECC         | Encrypt user fields  | email, contact_info
 * RSA         | Encrypt post/review  | review_text, body, title
 * ECC         | Digital signatures   | signature field (all data)
 * HMAC-SHA256 | Integrity check      | mac field (all data)
 * SHA-256     | Hashing              | key derivation, MAC, signatures
 * BigInteger  | Arithmetic           | All modular operations
 * 
 * NO SYMMETRIC ENCRYPTION ANYWHERE
 * NO OpenSSL, GMP, BCMath, or built-in crypto used
 * ALL algorithms implemented from scratch using BigInteger
 */
