<?php

namespace App\Console\Commands;

use App\Services\Crypto\KeyManager;
use Illuminate\Console\Command;

class GenerateCryptoKeys extends Command
{
    protected $signature = 'crypto:generate-keys';
    protected $description = 'Generate and rotate encryption key material';

    public function handle(): int
    {
        $this->info('Generating RSA and ECC key pairs...');
        $manager = app(KeyManager::class);
        $manager->rotateKeys();
        $this->info('✓ Encryption keys generated successfully.');

        return Command::SUCCESS;
    }
}
