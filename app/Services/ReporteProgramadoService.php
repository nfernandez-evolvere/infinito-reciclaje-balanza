<?php

namespace App\Services;

use App\Models\ReporteProgramado;
use App\Repositories\ReporteDestinatarioRepository;
use App\Repositories\ReporteProgramadoRepository;

class ReporteProgramadoService
{
    public function __construct(
        protected ReporteProgramadoRepository $programadoRepository,
        protected ReporteDestinatarioRepository $destinatarioRepository,
    ) {}

    public function create(array $validated): ReporteProgramado
    {
        $data = $this->prepareData($validated);
        $programado = $this->programadoRepository->create($data);
        $this->syncDestinatarios($data['destinatarios']);

        return $programado;
    }

    public function update(ReporteProgramado $programado, array $validated): ReporteProgramado
    {
        $data = $this->prepareData($validated);
        $programado = $this->programadoRepository->update($programado, $data);
        $this->syncDestinatarios($data['destinatarios']);

        return $programado;
    }

    public function delete(ReporteProgramado $programado): void
    {
        $this->programadoRepository->delete($programado);
    }

    private function prepareData(array $validated): array
    {
        $destinatarios = array_values(array_filter(
            array_map('trim', explode(',', $validated['destinatarios']))
        ));

        return [
            ...$validated,
            'destinatarios'    => $destinatarios,
            'cron_expresion'   => $this->cronDesdeFrecuencia($validated['frecuencia']),
            'proximo_envio_at' => now()->addMinute(),
        ];
    }

    private function cronDesdeFrecuencia(string $frecuencia): string
    {
        return match ($frecuencia) {
            'diaria'    => '0 8 * * *',
            'semanal'   => '0 8 * * 1',
            'quincenal' => '0 8 1,15 * *',
            default     => '0 8 1 * *',   // mensual
        };
    }

    private function syncDestinatarios(array $emails): void
    {
        $orgId = app()->bound('organizacion') ? app('organizacion')?->id : null;
        if (! $orgId) {
            return;
        }
        $this->destinatarioRepository->upsert($orgId, $emails);
    }
}
