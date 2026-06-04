<?php

namespace App\Policies;

use App\Models\Alerta;
use App\Models\User;

class AlertaPolicy
{
    public function update(User $user, Alerta $alerta): bool
    {
        return $user->isAdmin() && $user->organizacion_id === $alerta->organizacion_id;
    }
}
