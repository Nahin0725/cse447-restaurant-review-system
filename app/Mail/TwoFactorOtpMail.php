<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TwoFactorOtpMail extends Mailable
{
    public function __construct(public string $otp, public string $userName)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Restaurant Review Account Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two_factor_otp',
            with: [
                'otp' => $this->otp,
                'userName' => $this->userName,
            ],
        );
    }
}
