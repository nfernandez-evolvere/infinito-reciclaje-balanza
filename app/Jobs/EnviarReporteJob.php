<?php

namespace App\Jobs;

use App\Models\Organizacion;
use App\Models\ReporteGenerado;
use App\Services\ReporteEnvioService;
use App\Services\ReporteGeneradoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Segunda fase del pipeline de reportes programados: envía por email un
 * registro ya generado, renderizando desde su snapshot congelado. Lo despachan
 * el camino directo (GenerarReporteJob), la aprobación de una revisión y el
 * reintento de un envío fallido — los tres con el mismo resultado idéntico.
 */
class EnviarReporteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly int $generadoId,
    ) {}

    public function handle(ReporteEnvioService $envioService): void
    {
        Log::info('EnviarReporteJob: iniciando', ['generado_id' => $this->generadoId]);

        $generado = ReporteGenerado::withoutGlobalScopes()->findOrFail($this->generadoId);

        // Guard de idempotencia: un attempt previo ya lo envió, o el registro
        // fue procesado por otra vía. Nunca duplicar el mail al municipio.
        if ($generado->estado !== ReporteGenerado::ESTADO_ENVIANDO) {
            Log::info('EnviarReporteJob: registro fuera de estado enviando, se omite', [
                'generado_id' => $generado->id,
                'estado'      => $generado->estado,
            ]);

            return;
        }

        // El render sale del snapshot, pero el job corre fuera de HTTP y la
        // vista PDF puede tocar helpers dependientes de la organización.
        $organizacion = Organizacion::find($generado->organizacion_id);
        if ($organizacion) {
            app()->instance('organizacion', $organizacion);
        }

        $envioService->enviar($generado);

        Log::info('EnviarReporteJob: completado', [
            'generado_id'   => $generado->id,
            'destinatarios' => $generado->destinatarios,
        ]);
    }

    /**
     * Fallo definitivo del envío: el registro queda fallido pero CONSERVA su
     * snapshot — el reintento desde la UI reenvía el mismo contenido sin
     * regenerar ni re-llamar a la IA.
     */
    public function failed(Throwable $exception): void
    {
        $generado = ReporteGenerado::withoutGlobalScopes()->find($this->generadoId);

        if (! $generado) {
            return;
        }

        app(ReporteGeneradoService::class)->marcarFallo($generado, $exception->getMessage());
    }
}
