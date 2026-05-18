<?php

namespace App\Services;

use App\Models\Zona;
use App\Models\ZonaServicio;
use App\Repositories\ZonaRepository;
use Illuminate\Database\Eloquent\Collection;

class ZonaService
{
    public function __construct(
        protected ZonaRepository $repository,
    ) {}

    public function listar(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    public function crear(array $data): Zona
    {
        return $this->repository->create($data);
    }

    public function actualizar(Zona $zona, array $data): Zona
    {
        return $this->repository->update($zona, $data);
    }

    public function desactivar(Zona $zona): void
    {
        $this->repository->deactivate($zona);
    }

    public function activar(Zona $zona): void
    {
        $this->repository->activate($zona);
    }

    public function eliminar(Zona $zona): void
    {
        $this->repository->delete($zona);
    }

    public function asignarServicio(Zona $zona, int $tipoServicioId, array $turnos, array $horarios): ZonaServicio
    {
        return $this->repository->assignServicio($zona, $tipoServicioId, $turnos, $horarios);
    }

    public function actualizarServicio(Zona $zona, int $tipoServicioId, array $turnos, array $horarios): void
    {
        $this->repository->updateServicio($zona, $tipoServicioId, $turnos, $horarios);
    }

    public function quitarServicio(Zona $zona, int $tipoServicioId): void
    {
        $this->repository->removeServicio($zona, $tipoServicioId);
    }
}
