<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vehiculo;
use App\Repositories\PesajeRepository;
use App\Repositories\VehiculoLogRepository;
use App\Repositories\VehiculoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VehiculoService
{
    public function __construct(
        protected VehiculoRepository $repository,
        protected VehiculoLogRepository $logRepository,
        protected PesajeRepository $pesajeRepository,
    ) {}

    public function listar(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function crear(array $data): Vehiculo
    {
        return $this->repository->create($data);
    }

    public function update(Vehiculo $vehiculo, array $data, User $usuario): Vehiculo
    {
        $taraAnterior = (int) $vehiculo->tara_kg;
        $taraNueva    = (int) $data['tara_kg'];

        $intencion = $data['_intencion_tara'] ?? null;
        $motivo    = trim((string) ($data['_motivo_tara'] ?? ''));

        // Los campos de decisión no son columnas del vehículo.
        $payload = collect($data)->except(['_intencion_tara', '_motivo_tara'])->all();

        return DB::transaction(function () use ($vehiculo, $payload, $taraAnterior, $taraNueva, $intencion, $motivo, $usuario) {
            $this->repository->update($vehiculo, $payload);

            if ($taraNueva !== $taraAnterior) {
                $this->logRepository->create([
                    'vehiculo_id'    => $vehiculo->id,
                    'campo'          => 'tara_kg',
                    'valor_anterior' => (string) $taraAnterior,
                    'valor_nuevo'    => (string) $taraNueva,
                    'motivo'         => $this->motivoAuditoria($intencion, $motivo),
                    'usuario_id'     => $usuario->id,
                ]);

                if ($intencion === 'corregir_dato') {
                    $this->recalcularPesajes($vehiculo, $taraNueva, $motivo, $usuario);
                }
            }

            return $vehiculo->fresh();
        });
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

    /**
     * Recalcula la tara y el neto de todos los pesajes no cancelados del vehículo,
     * dejando una entrada de auditoría por cada campo modificado.
     *
     * Delega en una operación set-based del repositorio: el costo es constante
     * (3 sentencias) sin importar cuántos pesajes históricos tenga el vehículo.
     */
    private function recalcularPesajes(Vehiculo $vehiculo, int $taraNueva, string $motivo, User $usuario): void
    {
        $motivoPesaje = 'Corrección de tara del padrón'
            . ($motivo !== '' ? ' — ' . $motivo : '');

        $this->pesajeRepository->recalcularPorCambioDeTara(
            $vehiculo->id,
            $taraNueva,
            $motivoPesaje,
            $usuario->id,
        );
    }

    private function motivoAuditoria(?string $intencion, string $motivo): string
    {
        $sufijo = $motivo !== '' ? ' — ' . $motivo : '';

        return match ($intencion) {
            'corregir_dato' => 'Corrección de dato mal cargado' . $sufijo,
            'cambio_real'   => 'Cambio real de tara' . $sufijo,
            default         => $motivo !== '' ? $motivo : 'Actualización de tara',
        };
    }
}
