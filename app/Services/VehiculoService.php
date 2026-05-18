<?php

namespace App\Services;

use App\Models\Vehiculo;
use App\Repositories\VehiculoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VehiculoService
{
    public function __construct(
        protected VehiculoRepository $repository,
    ) {}

    public function listar(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function crear(array $data): Vehiculo
    {
        return $this->repository->create($data);
    }

    public function actualizar(Vehiculo $vehiculo, array $data): Vehiculo
    {
        return $this->repository->update($vehiculo, $data);
    }

    public function desactivar(Vehiculo $vehiculo): void
    {
        $this->repository->deactivate($vehiculo);
    }

    public function activar(Vehiculo $vehiculo): void
    {
        $this->repository->activate($vehiculo);
    }

    public function eliminar(Vehiculo $vehiculo): void
    {
        $this->repository->delete($vehiculo);
    }
}
