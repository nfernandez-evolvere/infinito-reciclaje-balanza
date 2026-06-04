<?php

namespace App\Services;

use App\Models\ReporteGenerado;
use App\Models\ReporteProgramado;
use App\Repositories\ReporteGeneradoRepository;
use Carbon\Carbon;

/**
 * Registra cada reporte producido en el historial. No genera el contenido:
 * solo persiste los metadatos que permiten volver a generarlo (período,
 * filtros, formato) y auditar quién/cuándo lo descargó o lo recibió.
 */
class ReporteGeneradoService
{
    public function __construct(
        protected ReporteGeneradoRepository $repository,
    ) {}

    /**
     * Descarga manual desde la pantalla de reportes (Excel o PDF).
     * organizacion_id y usuario_id los resuelve el contexto HTTP.
     *
     * @param  array<string, int>  $filtros
     */
    public function registrarDescarga(
        string $formato,
        string $tipo,
        Carbon $desde,
        Carbon $hasta,
        array $filtros = [],
        ?string $conclusiones = null,
    ): ReporteGenerado {
        return $this->repository->create([
            'usuario_id'    => auth()->id(),
            'origen'        => 'manual',
            'tipo'          => $tipo,
            'formato'       => $formato,
            'periodo_desde' => $desde,
            'periodo_hasta' => $hasta,
            'filtros'       => $filtros ?: null,
            'estado'        => 'generado',
            'conclusiones'  => $conclusiones,
        ]);
    }

    /**
     * Envío programado completado. Se llama desde el job, fuera del contexto
     * HTTP: por eso organizacion_id se pasa explícito (no hay app('organizacion')).
     *
     * @param  list<string>  $destinatarios
     */
    public function registrarEnvio(
        ReporteProgramado $programado,
        Carbon $desde,
        Carbon $hasta,
        array $destinatarios,
        ?string $conclusiones = null,
    ): ReporteGenerado {
        return $this->repository->create([
            ...$this->datosBase($programado, $desde, $hasta),
            'destinatarios' => $destinatarios,
            'estado'        => 'enviado',
            'conclusiones'  => $conclusiones,
        ]);
    }

    /** Envío programado que agotó los reintentos. Deja el detalle del error. */
    public function registrarFallo(
        ReporteProgramado $programado,
        Carbon $desde,
        Carbon $hasta,
        string $error,
    ): ReporteGenerado {
        return $this->repository->create([
            ...$this->datosBase($programado, $desde, $hasta),
            'destinatarios' => $programado->destinatarios,
            'estado'        => 'fallido',
            'error'         => mb_substr($error, 0, 500),
        ]);
    }

    /**
     * Campos comunes a envíos programados (éxito y fallo).
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
