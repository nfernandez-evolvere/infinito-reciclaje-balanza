<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Aviso a los admins de la organización: un reporte programado quedó
 * pendiente de revisión y no se enviará hasta que alguien lo apruebe.
 */
class ReportePendienteRevisionMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly string $nombreReporte,
        public readonly string $periodo,
        public readonly string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reporte pendiente de revisión — {$this->periodo}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reporte-pendiente-revision',
        );
    }
}
