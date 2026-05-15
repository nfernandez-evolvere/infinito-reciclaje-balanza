<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PesajeResumenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Collection $pesajes,
        public readonly string $periodo,
        public readonly float $pesoTotalKg,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resumen de pesajes — '.$this->periodo.' — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pesaje-resumen',
        );
    }
}
