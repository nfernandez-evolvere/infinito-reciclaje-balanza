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
     * Período que cubre el reporte: el intervalo completo anterior a la corrida.
     * Mensual anclado el 1/08 cubre julio entero (1/07 00:00 → 31/07 23:59);
     * el día de la corrida nunca se incluye (a las 08:00 casi no tiene datos).
     * Sin $corrida (descargas manuales, "enviar ahora") se calcula desde hoy.
     *
     * @return array{0: Carbon, 1: Carbon} [$desde, $hasta]
     */
    public function calcularPeriodo(string $frecuencia, ?Carbon $corrida = null): array
    {
        $corrida ??= now();
        $hasta = $corrida->copy()->subDay()->endOfDay();

        $desde = match ($frecuencia) {
            'diaria'    => $corrida->copy()->subDay(),
            'semanal'   => $corrida->copy()->subDays(7),
            'quincenal' => $corrida->copy()->subDays(15),
            default     => $corrida->copy()->subMonthNoOverflow(), // mensual
        };

        return [$desde->startOfDay(), $hasta];
    }

    /**
     * Próxima corrida a partir de la que acaba de vencer (proximo_envio_at):
     * la fecha elegida como primer envío define la fase del cronograma (elegís
     * el 5 → corre todos los 5). El loop absorbe downtime prolongado sin
     * disparar una ráfaga de envíos atrasados. Mensual recupera el día ancla
     * de inicio_en tras meses cortos (31/01 → 28/02 → 31/03).
     */
    public function calcularProximoEnvio(ReporteProgramado $programado): Carbon
    {
        // Hora normalizada ANTES de iterar: normalizar después podría retroceder
        // el resultado a antes de now() (anclas legacy con hora distinta de las 08:00).
        $proximo = ($programado->proximo_envio_at ?? now())->copy()->setTime(8, 0);

        do {
            $proximo = $this->sumarIntervalo($proximo, $programado->frecuencia, $programado->inicio_en?->day);
        } while ($proximo->lte(now()));

        return $proximo;
    }

    /**
     * Avanza proximo_envio_at al despachar la generación (no al enviar): así el
     * scheduler no re-despacha cada 15 minutos mientras el reporte se genera o
     * espera revisión. ultimo_envio_at se actualiza recién con el envío real.
     */
    public function avanzarProximoEnvio(ReporteProgramado $programado): void
    {
        $this->programadoRepository->update($programado, [
            'proximo_envio_at' => $this->calcularProximoEnvio($programado),
        ]);
    }

    private function sumarIntervalo(Carbon $desde, string $frecuencia, ?int $diaAncla): Carbon
    {
        if ($frecuencia === 'mensual') {
            $siguiente = $desde->copy()->addMonthNoOverflow();

            // El día ancla (del primer envío) se reimpone cada mes: sin esto,
            // un ancla 31 quedaría clavada en 28 después de pasar por febrero.
            return $siguiente->setDay(min($diaAncla ?? $desde->day, $siguiente->daysInMonth));
        }

        return match ($frecuencia) {
            'diaria'  => $desde->copy()->addDay(),
            'semanal' => $desde->copy()->addDays(7),
            default   => $desde->copy()->addDays(15), // quincenal
        };
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

        // La fecha elegida como primer envío ancla el cronograma: el primer
        // disparo es ese día a las 08:00 (si ya pasaron, el scheduler lo levanta
        // en su próximo tick). En edición el modal manda la fecha del próximo
        // envío prefijada: guardar sin tocarla re-ancla al mismo día — editar
        // ya no re-dispara el envío.
        return [
            ...$validated,
            'destinatarios'    => $destinatarios,
            'opciones'         => $opciones,
            'cron_expresion'   => $this->cronDesdeFrecuencia($validated['frecuencia']),
            'proximo_envio_at' => Carbon::parse($validated['inicio_en'])->setTime(8, 0),
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
