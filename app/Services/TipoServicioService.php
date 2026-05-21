<?php

namespace App\Services;

use App\Models\TipoServicio;
use App\Repositories\TipoServicioRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TipoServicioService
{
    public function __construct(
        protected TipoServicioRepository $repository,
    ) {}

    public function listar(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function crear(array $data): TipoServicio
    {
        $ids = array_map('intval', $data['tipo_vehiculo_ids'] ?? []);
        unset($data['tipo_vehiculo_ids']);
        return $this->repository->create($data, $ids);
    }

    public function actualizar(TipoServicio $tipoServicio, array $data): TipoServicio
    {
        $ids = array_map('intval', $data['tipo_vehiculo_ids'] ?? []);
        unset($data['tipo_vehiculo_ids']);
        return $this->repository->update($tipoServicio, $data, $ids);
    }

    public function desactivar(TipoServicio $tipoServicio): void
    {
        $this->repository->deactivate($tipoServicio);
    }

    public function activar(TipoServicio $tipoServicio): void
    {
        $this->repository->activate($tipoServicio);
    }

    public function eliminar(TipoServicio $tipoServicio): void
    {
        $this->repository->delete($tipoServicio);
    }
}
