<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReporteAlertaMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly string $periodo,
        public readonly string $municipalidad,
        public readonly string $pdfContent,
        public readonly string $filename,
        public readonly int $totalAlertas,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte de Alertas — {$this->periodo}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reporte-alertas',
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
