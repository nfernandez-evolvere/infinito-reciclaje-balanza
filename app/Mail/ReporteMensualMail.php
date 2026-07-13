<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReporteMensualMail extends Mailable
{
    use Queueable;

    /**
     * @param  list<array{content: string, filename: string, mime: string}>  $adjuntos
     *                                                                                  Uno o más archivos del reporte (PDF y/o Excel) según lo configurado.
     */
    public function __construct(
        public readonly string $periodo,
        public readonly string $municipalidad,
        public readonly array $adjuntos,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte de Pesajes — {$this->periodo}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reporte-mensual',
            with: ['nombresAdjuntos' => array_column($this->adjuntos, 'filename')],
        );
    }

    public function attachments(): array
    {
        return array_map(
            fn (array $a) => Attachment::fromData(fn () => $a['content'], $a['filename'])
                ->withMime($a['mime']),
            $this->adjuntos,
        );
    }
}
