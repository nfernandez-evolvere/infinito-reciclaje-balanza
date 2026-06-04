<?php

namespace App\Policies;

use App\Models\Alerta;
use App\Models\User;

class AlertaPolicy
{
    public function update(User $user, Alerta $alerta): bool
    {
        /** @var \App\Models\Organizacion $org */
        $org = app('organizacion');

        return $user->isAdmin() && $org->id === $alerta->organizacion_id;
    }
}
