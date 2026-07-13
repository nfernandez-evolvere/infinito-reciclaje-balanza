<?php

namespace App\Services;

use App\Models\ReporteProgramado;
use App\Repositories\ReporteDestinatarioRepository;
use App\Repositories\ReporteProgramadoRepository;
use App\Support\ReporteSecciones;
use Carbon\Carbon;

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
        $data = $this->prepareData($validated, $programado);
        $programado = $this->programadoRepository->update($programado, $data);
        $this->syncDestinatarios($data['destinatarios']);

        return $programado;
    }

    public function delete(ReporteProgramado $programado): void
    {
        $this->programadoRepository->delete($programado);
    }

    public function toggleActivo(ReporteProgramado $programado): ReporteProgramado
    {
        return $this->programadoRepository->update($programado, ['activo' => ! $programado->activo]);
    }

    /**
     * Período que cubre el reporte según la frecuencia del programado.
     *
     * @return array{0: Carbon, 1: Carbon} [$desde, $hasta]
     */
    public function calcularPeriodo(string $frecuencia): array
    {
        return match ($frecuencia) {
            'diaria'    => [now()->subDay()->startOfDay(),    now()->subDay()->endOfDay()],
            'semanal'   => [now()->subDays(7)->startOfDay(),  now()->endOfDay()],
            'quincenal' => [now()->subDays(15)->startOfDay(), now()->endOfDay()],
            default     => [now()->subDays(30)->startOfDay(), now()->endOfDay()], // mensual
        };
    }

    public function calcularProximoEnvio(string $frecuencia): Carbon
    {
        return match ($frecuencia) {
            'diaria'    => now()->addDay()->setTime(8, 0),
            'semanal'   => now()->next(Carbon::MONDAY)->setTime(8, 0),
            'quincenal' => $this->proximoQuincenal(),
            default     => now()->addMonthNoOverflow()->startOfMonth()->setTime(8, 0), // mensual
        };
    }

    /**
     * Avanza proximo_envio_at al despachar la generación (no al enviar): así el
     * scheduler no re-despacha cada 15 minutos mientras el reporte se genera o
     * espera revisión. ultimo_envio_at se actualiza recién con el envío real.
     */
    public function avanzarProximoEnvio(ReporteProgramado $programado): void
    {
        $this->programadoRepository->update($programado, [
            'proximo_envio_at' => $this->calcularProximoEnvio($programado->frecuencia),
        ]);
    }

    private function proximoQuincenal(): Carbon
    {
        return now()->day < 15
            ? now()->setDay(15)->setTime(8, 0)
            : now()->addMonthNoOverflow()->startOfMonth()->setTime(8, 0);
    }

    private function prepareData(array $validated, ?ReporteProgramado $existing = null): array
    {
        $destinatarios = array_values(array_filter(
            array_map('trim', explode(',', $validated['destinatarios']))
        ));

        // 'formatos' y 'revision' no son columnas: se guardan dentro del JSON
        // 'opciones'. Las alertas se envían siempre en PDF; el informe mensual
        // usa lo elegido. 'revision' sobreescribe el default global ('heredar'
        // cae a config.revision_requerida).
        $formatos = $validated['tipo'] === 'informe_mensual'
            ? array_values(array_intersect(['pdf', 'excel'], $validated['formatos'] ?? ['pdf']))
            : ['pdf'];
        $opcionesBase = $existing ? ($existing->opciones ?? []) : [];
        $opciones = [
            ...$opcionesBase,
            'formatos' => $formatos ?: ['pdf'],
            'revision' => $validated['revision'] ?? 'heredar',
        ];

        // 'secciones' también vive en opciones: solo cuando el programado las
        // personaliza (informe mensual); sin personalizar se remueve la clave y
        // el programado vuelve a heredar la configuración general. Al personalizar,
        // una lista pdf ausente significa "sin páginas de contenido" ([]), nunca
        // "todas" — por eso no se pasa null a sanitizar.
        $personaliza = ($validated['secciones_personalizadas'] ?? false) && $validated['tipo'] === 'informe_mensual';
        if ($personaliza) {
            $opciones['secciones'] = ReporteSecciones::sanitizar([
                'pdf'   => $validated['secciones']['pdf'] ?? [],
                'excel' => $validated['secciones']['excel'] ?? [],
            ]);
        } else {
            unset($opciones['secciones']);
        }
        unset($validated['formatos'], $validated['revision'], $validated['secciones'], $validated['secciones_personalizadas']);

        return [
            ...$validated,
            'destinatarios'    => $destinatarios,
            'opciones'         => $opciones,
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
