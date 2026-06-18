<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemporaryPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $temporaryPassword,
        public string $changePasswordUrl,
        public string $appStoreUrl,
        public string $googlePlayUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mật khẩu tạm thời có hiệu lực 1 phút',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.temporary-password',
        );
    }
}
