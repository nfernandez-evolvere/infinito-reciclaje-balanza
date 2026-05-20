<?php

namespace App\Mail;

use App\Models\Organizacion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly ?string $temporaryPassword = null,
        public readonly ?Organizacion $organizacion = null,
    ) {}

    public function envelope(): Envelope
    {
        $nombre = $this->organizacion?->nombre ?? config('app.name');

        return new Envelope(
            subject: 'Bienvenido/a a '.$nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome',
        );
    }
}
