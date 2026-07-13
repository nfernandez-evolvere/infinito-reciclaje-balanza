<?php

namespace App\Services;

use App\Models\Zona;
use App\Repositories\ZonaRepository;

class ZonaService
{
    public function __construct(
        protected ZonaRepository $repository,
    ) {}

    public function crear(array $data, array $turnos = [], array $horarios = []): Zona
    {
        return $this->repository->create($data, $turnos, $horarios);
    }

    public function actualizar(Zona $zona, array $data, array $turnos = [], array $horarios = []): Zona
    {
        return $this->repository->update($zona, $data, $turnos, $horarios);
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
}
