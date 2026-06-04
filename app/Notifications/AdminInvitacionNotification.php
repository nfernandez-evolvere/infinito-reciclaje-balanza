<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AdminInvitacionNotification extends ResetPassword implements ShouldQueue
{
    public int $tries = 3;
    public function __construct(
        string $token,
        protected string $orgNombre,
    ) {
        parent::__construct($token);
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Activá tu cuenta — '.$this->orgNombre)
            ->markdown('emails.admin-invitacion', [
                'url'           => $url,
                'expireMinutes' => $expireMinutes,
                'user'          => $notifiable,
                'orgNombre'     => $this->orgNombre,
            ]);
    }
}
