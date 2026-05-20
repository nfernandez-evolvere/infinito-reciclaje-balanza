<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        $orgNombre = $notifiable->organizacion?->nombre ?? config('app.name');

        return (new MailMessage)
            ->subject('Verificá tu correo electrónico — '.$orgNombre)
            ->markdown('emails.verify-email', [
                'url'       => $url,
                'user'      => $notifiable,
                'orgNombre' => $orgNombre,
            ]);
    }
}
