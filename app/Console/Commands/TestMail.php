<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorOtpMail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test 2FA email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = rand(100000, 999999);
        Mail::to('nahin.afrin@g.bracu.ac.bd')->send(new TwoFactorOtpMail($token, 'Test User'));
        $this->info('Test email sent to nahin.afrin@g.bracu.ac.bd with OTP: ' . $token);
    }
}
