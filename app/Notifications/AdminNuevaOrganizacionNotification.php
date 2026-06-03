<?php

namespace App\Notifications;

use App\Models\Organizacion;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNuevaOrganizacionNotification extends Notification
{
    public function __construct(
        protected Organizacion $organizacion,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva organización asignada — '.$this->organizacion->nombre)
            ->markdown('emails.admin-nueva-organizacion', [
                'user'         => $notifiable,
                'organizacion' => $this->organizacion,
            ]);
    }
}
