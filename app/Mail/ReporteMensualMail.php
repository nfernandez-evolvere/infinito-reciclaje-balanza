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

    public function __construct(
        public readonly string $periodo,
        public readonly string $municipalidad,
        public readonly string $pdfContent,
        public readonly string $filename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Informe de Pesajes — {$this->periodo}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reporte-mensual',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
