<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        $orgNombre = $notifiable->organizacion?->nombre ?? config('app.name');

        return (new MailMessage)
            ->subject('Restablecer contraseña — '.$orgNombre)
            ->markdown('emails.reset-password', [
                'url'           => $url,
                'expireMinutes' => $expireMinutes,
                'user'          => $notifiable,
                'orgNombre'     => $orgNombre,
            ]);
    }
}
