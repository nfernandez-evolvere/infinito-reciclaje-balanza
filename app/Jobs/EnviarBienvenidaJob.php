<?php

namespace App\Jobs;

use App\Mail\WelcomeMail;
use App\Models\Organizacion;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class EnviarBienvenidaJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        protected User $usuario,
        protected string $plainPassword,
        protected Organizacion $organizacion,
    ) {}

    public function handle(): void
    {
        Mail::to($this->usuario)->send(new WelcomeMail(
            $this->usuario,
            $this->plainPassword,
            $this->organizacion,
        ));
    }
}
