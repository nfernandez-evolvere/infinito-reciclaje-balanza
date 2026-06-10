<?php

namespace App\Services;

use App\Jobs\EnviarReporteJob;
use App\Jobs\GenerarReporteJob;
use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Models\User;
use App\Repositories\ReporteGeneradoRepository;
use Carbon\Carbon;

/**
 * Orquesta el ciclo de vida de cada reporte producido y su máquina de estados:
 * generando → en_revision|enviando → enviado; en_revision → descartado;
 * generando/enviando → fallido → (reintento) generando|enviando. Todas las
 * transiciones pasan por el lock optimista del repository, así una carrera
 * (doble aprobación, attempts concurrentes del job) nunca duplica un envío.
 */
class ReporteGeneradoService
{
    public function __construct(
        protected ReporteGeneradoRepository $repository,
        protected ReporteProgramadoService $programadoService,
    ) {}

    /**
     * Descarga manual desde la pantalla de reportes (Excel o PDF).
     * organizacion_id y usuario_id los resuelve el contexto HTTP.
     *
     * @param  array<string, int>  $filtros
     * @param  array<string, mixed>|null  $snapshot  Datos congelados para re-descargar idéntico.
     */
    public function registrarDescarga(
        string $formato,
        string $tipo,
        Carbon $desde,
        Carbon $hasta,
        array $filtros = [],
        ?string $conclusiones = null,
        ?array $snapshot = null,
    ): ReporteGenerado {
        return $this->repository->create([
            'usuario_id'    => auth()->id(),
            'origen'        => 'manual',
            'tipo'          => $tipo,
            'formato'       => $formato,
            'periodo_desde' => $desde,
            'periodo_hasta' => $hasta,
            'filtros'       => $filtros ?: null,
            'estado'        => ReporteGenerado::ESTADO_GENERADO,
            'conclusiones'  => $conclusiones,
            'snapshot'      => $snapshot,
        ]);
    }

    /**
     * Punto de entrada de todo envío programado (scheduler y "enviar ahora"):
     * crea el registro en estado 'generando' con el período calculado y los
     * destinatarios/formato congelados, avanza proximo_envio_at ANTES de que
     * corra el job (el scheduler no debe re-despachar mientras se genera o
     * espera revisión) y despacha la generación. El período queda en el
     * registro: un reintento días después reproduce el mismo rango, nunca
     * se recalcula desde now().
     */
    public function iniciarGeneracion(ReporteProgramado $programado, bool $avanzarProximo = true): ReporteGenerado
    {
        [$desde, $hasta] = $this->programadoService->calcularPeriodo($programado->frecuencia);

        $generado = $this->repository->create([
            ...$this->datosBase($programado, $desde, $hasta),
            'destinatarios' => $programado->destinatarios,
            'estado'        => ReporteGenerado::ESTADO_GENERANDO,
        ]);

        if ($avanzarProximo) {
            $this->programadoService->avanzarProximoEnvio($programado);
        }

        GenerarReporteJob::dispatch($generado->id);

        return $generado;
    }

    /** Aprueba un reporte en revisión y encola su envío. False = conflicto concurrente. */
    public function aprobar(ReporteGenerado $generado, User $revisor): bool
    {
        $aprobado = $this->repository->transicionar($generado, [ReporteGenerado::ESTADO_EN_REVISION], [
            'estado'          => ReporteGenerado::ESTADO_ENVIANDO,
            'revisado_por_id' => $revisor->id,
            'revisado_at'     => now(),
        ]);

        if ($aprobado) {
            EnviarReporteJob::dispatch($generado->id);
        }

        return $aprobado;
    }

    /** Descarta un reporte en revisión (terminal; queda en el historial como auditoría). */
    public function descartar(ReporteGenerado $generado, User $revisor, ?string $motivo = null): bool
    {
        return $this->repository->transicionar($generado, [ReporteGenerado::ESTADO_EN_REVISION], [
            'estado'          => ReporteGenerado::ESTADO_DESCARTADO,
            'motivo_descarte' => $motivo,
            'revisado_por_id' => $revisor->id,
            'revisado_at'     => now(),
        ]);
    }

    /**
     * Reintenta un reporte fallido. Con snapshot solo falló el envío: se
     * re-encola desde los datos congelados (sin regenerar ni re-llamar a la
     * IA). Sin snapshot falló la generación: se re-encola la generación
     * sobre el mismo registro, con su período original.
     */
    public function reintentar(ReporteGenerado $generado): bool
    {
        $haySnapshot = $generado->snapshot !== null;

        $reintentado = $this->repository->transicionar($generado, [ReporteGenerado::ESTADO_FALLIDO], [
            'estado' => $haySnapshot ? ReporteGenerado::ESTADO_ENVIANDO : ReporteGenerado::ESTADO_GENERANDO,
            'error'  => null,
        ]);

        if ($reintentado) {
            $haySnapshot
                ? EnviarReporteJob::dispatch($generado->id)
                : GenerarReporteJob::dispatch($generado->id);
        }

        return $reintentado;
    }

    /**
     * Edición de la narrativa IA durante la revisión: actualiza la columna y
     * el snapshot (el preview y el envío salen de ahí), preservando el texto
     * original de la IA la primera vez para auditoría.
     */
    public function actualizarConclusiones(ReporteGenerado $generado, ?string $conclusiones): bool
    {
        $snapshot = $generado->snapshot ?? [];
        $previas = $snapshot['conclusiones'] ?? [];

        if (! array_key_exists('original', $previas)) {
            $previas['original'] = $previas['analisis'] ?? $generado->conclusiones;
        }

        $previas['analisis'] = $conclusiones;
        $snapshot['conclusiones'] = $previas;

        return $this->repository->transicionar($generado, [ReporteGenerado::ESTADO_EN_REVISION], [
            'conclusiones' => $conclusiones,
            'snapshot'     => $snapshot,
        ]);
    }

    // ── Transiciones invocadas por los jobs ──────────────────────────────────

    /**
     * Generación completada con revisión requerida: congela el resultado y
     * queda a la espera de aprobación manual.
     *
     * @param  array<string, mixed>  $snapshot
     */
    public function marcarEnRevision(ReporteGenerado $generado, ?string $conclusiones, array $snapshot): bool
    {
        return $this->repository->transicionar($generado, [ReporteGenerado::ESTADO_GENERANDO], [
            'estado'       => ReporteGenerado::ESTADO_EN_REVISION,
            'conclusiones' => $conclusiones,
            'snapshot'     => $snapshot,
        ]);
    }

    /**
     * Generación completada en camino directo: congela el resultado y deja el
     * registro listo para que EnviarReporteJob lo despache.
     *
     * @param  array<string, mixed>  $snapshot
     */
    public function marcarListoParaEnvio(ReporteGenerado $generado, ?string $conclusiones, array $snapshot): bool
    {
        return $this->repository->transicionar($generado, [ReporteGenerado::ESTADO_GENERANDO], [
            'estado'       => ReporteGenerado::ESTADO_ENVIANDO,
            'conclusiones' => $conclusiones,
            'snapshot'     => $snapshot,
        ]);
    }

    public function marcarEnviado(ReporteGenerado $generado): bool
    {
        return $this->repository->transicionar($generado, [ReporteGenerado::ESTADO_ENVIANDO], [
            'estado'     => ReporteGenerado::ESTADO_ENVIADO,
            'enviado_at' => now(),
        ]);
    }

    /**
     * Fallo definitivo de generación o envío (failed() del job): update in
     * place — nunca crea filas nuevas, el registro queda reintetable.
     */
    public function marcarFallo(ReporteGenerado $generado, string $error): bool
    {
        return $this->repository->transicionar(
            $generado,
            [ReporteGenerado::ESTADO_GENERANDO, ReporteGenerado::ESTADO_ENVIANDO],
            [
                'estado' => ReporteGenerado::ESTADO_FALLIDO,
                'error'  => mb_substr($error, 0, 500),
            ],
        );
    }

    /**
     * Campos comunes de los registros de envíos programados.
     *
     * @return array<string, mixed>
     */
    private function datosBase(ReporteProgramado $programado, Carbon $desde, Carbon $hasta): array
    {
        return [
            'organizacion_id'       => $programado->organizacion_id,
            'reporte_programado_id' => $programado->id,
            'origen'                => 'programado',
            'tipo'                  => $programado->tipo,
            'formato'               => implode('+', $programado->formatos()),
            'periodo_desde'         => $desde,
            'periodo_hasta'         => $hasta,
        ];
    }
}
