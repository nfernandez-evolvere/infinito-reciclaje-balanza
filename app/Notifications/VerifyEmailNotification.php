<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verificá tu correo electrónico — '.config('app.name'))
            ->markdown('emails.verify-email', [
                'url' => $url,
                'user' => $notifiable,
            ]);
    }
}
