<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UsuarioRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $orgId = app('organizacion')->id;

        return User::query()
            ->whereHas('organizaciones', fn ($q) => $q->where('organizaciones.id', $orgId))
            ->when(
                ! empty($filters['buscar']),
                fn ($q) => $q->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%'.$filters['buscar'].'%')
                        ->orWhere('email', 'like', '%'.$filters['buscar'].'%');
                })
            )
            ->when(
                ! empty($filters['role']),
                fn ($q) => $q->where('role', $filters['role'])
            )
            ->when(
                isset($filters['activo']) && $filters['activo'] !== '',
                fn ($q) => $q->where('activo', (bool) $filters['activo'])
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->appends(array_filter($filters, fn ($v) => $v !== '' && $v !== null));
    }

    public function create(array $data): User
    {
        $user = User::create($data);
        $user->organizaciones()->attach(app('organizacion')->id);

        return $user;
    }

    public function update(User $usuario, array $data): User
    {
        $usuario->update($data);

        return $usuario;
    }

    public function activate(User $usuario): void
    {
        $usuario->update(['activo' => true]);
    }

    public function deactivate(User $usuario): void
    {
        $usuario->update(['activo' => false]);
    }

    public function resetPassword(User $usuario, string $password): void
    {
        $usuario->update(['password' => $password]);
    }

    public function getOperadoresDeLaOrg(): Collection
    {
        $orgId = app('organizacion')->id;

        return User::whereHas('organizaciones', fn ($q) => $q->where('organizaciones.id', $orgId))
            ->where('role', 'operador')
            ->orderBy('name')
            ->get();
    }

    public function searchByNameOrEmail(string $q, int $limit = 8): Collection
    {
        return User::query()
            ->where('role', '!=', 'super_admin')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'email']);
    }

    public function marcarOnboardingVisto(User $user): void
    {
        $user->update(['onboarding_visto' => true]);
    }
}
