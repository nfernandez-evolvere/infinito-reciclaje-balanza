<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UsuarioRepository;

class ProfileService
{
    public function __construct(
        protected UsuarioRepository $repository,
    ) {}

    public function actualizarNombre(User $user, string $nombre): User
    {
        return $this->repository->update($user, ['name' => $nombre]);
    }

    public function cambiarPassword(User $user, string $nuevaPassword): void
    {
        $this->repository->resetPassword($user, $nuevaPassword);
    }
}
