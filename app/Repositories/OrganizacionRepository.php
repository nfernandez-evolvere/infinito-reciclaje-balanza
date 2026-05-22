<?php

namespace App\Repositories;

use App\Models\Organizacion;
use Illuminate\Pagination\LengthAwarePaginator;

class OrganizacionRepository
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Organizacion::with('users')->orderBy('nombre')->paginate($perPage);
    }

    public function create(array $data): Organizacion
    {
        return Organizacion::create($data);
    }

    public function update(Organizacion $organizacion, array $data): Organizacion
    {
        $organizacion->update($data);
        return $organizacion;
    }

    public function delete(Organizacion $organizacion): void
    {
        $organizacion->delete();
    }

    public function toggleActivo(Organizacion $organizacion): Organizacion
    {
        $organizacion->update(['activo' => ! $organizacion->activo]);
        return $organizacion;
    }
}
