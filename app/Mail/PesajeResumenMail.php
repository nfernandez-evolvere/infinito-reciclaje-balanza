<?php

namespace App\Mail;

use App\Models\Organizacion;
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
        public readonly ?Organizacion $organizacion = null,
    ) {}

    public function envelope(): Envelope
    {
        $nombre = $this->organizacion?->nombre ?? config('app.name');

        return new Envelope(
            subject: 'Resumen de pesajes — '.$this->periodo.' — '.$nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pesaje-resumen',
        );
    }
}
