<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
 * Canal privado de notificaciones de reportes del propio usuario. Los eventos
 * de estado (en_revision, enviado, fallido) se emiten hacia el dueño del
 * reporte; solo ese usuario puede suscribirse a su canal.
 */
Broadcast::channel('user.{id}.reportes', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});
