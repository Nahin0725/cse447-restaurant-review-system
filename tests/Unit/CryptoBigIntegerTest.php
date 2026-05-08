<?php

namespace Tests\Unit;

use App\Services\Crypto\Asymmetric\ECC;
use App\Services\Crypto\Asymmetric\RSA;
use App\Services\Crypto\BigInteger;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class CryptoBigIntegerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
        $kernel = $app->make(Kernel::class);
        $kernel->bootstrap();

        Container::setInstance($app);
        Facade::setFacadeApplication($app);
    }

    public function test_big_integer_arithmetic_from_scratch(): void
    {
        $this->assertSame('4', BigInteger::add('2', '2'));
        $this->assertSame('0', BigInteger::sub('2', '2'));
        $this->assertSame('6', BigInteger::mul('2', '3'));
        $this->assertSame('4', BigInteger::div('12', '3'));
        $this->assertSame('1', BigInteger::mod('13', '4'));
    }

    public function test_modular_exponentiation_and_inverse_from_scratch(): void
    {
        $this->assertSame('8', BigInteger::modPow('5', '3', '13'));
        $this->assertSame('3', BigInteger::modInverse('9', '26'));
    }

    public function test_rsa_encrypt_and_decrypt_from_scratch(): void
    {
        $keys = RSA::generateKeys(512);

        $message = 'Hello RSA';
        $cipher = RSA::encrypt($message, ['n' => $keys['n'], 'e' => $keys['e']]);
        $plaintext = RSA::decrypt($cipher, ['n' => $keys['n'], 'd' => $keys['d']]);

        $this->assertSame($message, $plaintext);
    }

    public function test_ecc_generate_key_pair_from_scratch(): void
    {
        $pair = ECC::generateKeyPair();

        $this->assertArrayHasKey('private', $pair);
        $this->assertArrayHasKey('public', $pair);
        $this->assertIsString($pair['private']);
        $this->assertIsArray($pair['public']);
        $this->assertArrayHasKey('x', $pair['public']);
        $this->assertArrayHasKey('y', $pair['public']);
    }
}
