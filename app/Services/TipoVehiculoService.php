<?php

namespace App\Services;

use App\Models\TipoVehiculo;
use App\Repositories\TipoVehiculoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TipoVehiculoService
{
    public function __construct(
        protected TipoVehiculoRepository $repository,
    ) {}

    public function listar(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function crear(array $data): TipoVehiculo
    {
        return $this->repository->create($data);
    }

    public function actualizar(TipoVehiculo $tipoVehiculo, array $data): TipoVehiculo
    {
        return $this->repository->update($tipoVehiculo, $data);
    }

    public function desactivar(TipoVehiculo $tipoVehiculo): void
    {
        $this->repository->deactivate($tipoVehiculo);
    }

    public function activar(TipoVehiculo $tipoVehiculo): void
    {
        $this->repository->activate($tipoVehiculo);
    }
}
