<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UsuarioRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UsuarioService
{
    public function __construct(
        protected UsuarioRepository $repository,
    ) {}

    public function listar(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function crear(array $data): User
    {
        return $this->repository->create($data);
    }

    public function actualizar(User $usuario, array $data): User
    {
        return $this->repository->update($usuario, $data);
    }

    public function activar(User $usuario): void
    {
        $this->repository->activate($usuario);
    }

    public function desactivar(User $usuario): void
    {
        $this->repository->deactivate($usuario);
    }

    public function resetearPassword(User $usuario, string $password): void
    {
        $this->repository->resetPassword($usuario, $password);
    }
}
