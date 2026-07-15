<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorResetMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $resetUrl,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('2段階認証の再設定')
            ->text('mail.two-factor-reset');
    }
}
