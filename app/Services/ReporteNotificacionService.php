<?php

namespace App\Services;

use App\Events\ReporteEstadoActualizado;
use App\Models\Alerta;
use App\Models\ReporteGenerado;
use App\Repositories\AlertaRepository;
use App\Repositories\ReporteGeneradoRepository;
use App\Repositories\UsuarioRepository;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Traduce un cambio de estado del reporte (en_revision | enviado | fallido) en
 * una notificación in-app persistente (campana, vía tabla alertas) y un evento
 * de tiempo real (toast + refresco del historial) hacia el dueño del reporte.
 *
 * La alerta persistente es la capa durable: el broadcast es best-effort, así un
 * Reverb caído nunca rompe el job que ya hizo bien su trabajo.
 */
class ReporteNotificacionService
{
    public function __construct(
        protected AlertaRepository $alertaRepository,
        protected UsuarioRepository $usuarioRepository,
        protected ReporteGeneradoRepository $generadoRepository,
    ) {}

    public function notificar(ReporteGenerado $generado, string $evento): void
    {
        [$tipo, $titulo, $descripcion] = $this->contenido($generado, $evento);

        foreach ($this->destinatarios($generado, $evento) as $userId) {
            $alerta = $this->alertaRepository->crearDeReporte(
                organizacionId: $generado->organizacion_id,
                userId: $userId,
                tipo: $tipo,
                titulo: $titulo,
                descripcion: $descripcion,
                reporteGeneradoId: $generado->id,
            );

            $this->emitir($userId, $generado, $alerta);
        }
    }

    /**
     * Destinatarios del evento: el dueño del reporte (generación → quien lo
     * configuró/disparó; envío/fallo → quien aprobó, o el dueño). Si no hay
     * dueño conocido (programados legacy sin creado_por_id) se cae a los admins
     * de la organización, para que un pendiente nunca quede sin avisar a nadie.
     *
     * @return list<int>
     */
    private function destinatarios(ReporteGenerado $generado, string $evento): array
    {
        $userId = $evento === 'en_revision'
            ? $generado->usuario_id
            : ($generado->revisado_por_id ?? $generado->usuario_id);

        if ($userId) {
            return [$userId];
        }

        return $this->usuarioRepository
            ->adminsDeOrganizacion($generado->organizacion_id)
            ->pluck('id')
            ->all();
    }

    /**
     * Título y descripción de la notificación según el evento.
     *
     * @return array{0: string, 1: string, 2: string} [tipo, titulo, descripcion]
     */
    private function contenido(ReporteGenerado $generado, string $evento): array
    {
        $nombre = $generado->tipo === 'alertas' ? 'El reporte de alertas' : 'El informe mensual';
        $periodo = $generado->periodo_desde->format('d/m/Y').' al '.$generado->periodo_hasta->format('d/m/Y');

        return match ($evento) {
            'en_revision' => [
                Alerta::TIPO_REPORTE_REVISION,
                'Reporte listo para revisar',
                "{$nombre} del {$periodo} ya está generado. Revisalo antes de enviarlo a los destinatarios.",
            ],
            'enviado' => [
                Alerta::TIPO_REPORTE_ENVIADO,
                'Reporte enviado',
                "{$nombre} del {$periodo} se envió a los destinatarios.",
            ],
            default => [
                Alerta::TIPO_REPORTE_FALLIDO,
                'No se pudo procesar el reporte',
                "{$nombre} del {$periodo} falló. Reintentá desde el historial.",
            ],
        };
    }

    private function emitir(int $userId, ReporteGenerado $generado, Alerta $alerta): void
    {
        try {
            // event() (no broadcast()) para que el evento ShouldBroadcastNow sea
            // a la vez despachable y asertable con Event::fake en los tests.
            event(new ReporteEstadoActualizado($userId, [
                'reporte_id' => $generado->id,
                'estado'     => $generado->estado,
                'toast'      => [
                    'message'     => $alerta->titulo,
                    'description' => $alerta->descripcion,
                    'variant'     => $alerta->tipoVariant(),
                ],
                'alerta' => [
                    'id'           => $alerta->id,
                    'tipo'         => $alerta->tipo,
                    'tipo_label'   => $alerta->tipoLabel(),
                    'tipo_variant' => $alerta->tipoVariant(),
                    'titulo'       => $alerta->titulo,
                    'descripcion'  => $alerta->descripcion,
                    'hace'         => $alerta->created_at->diffForHumans(),
                    'url'          => route('admin.reportes.index', ['tab' => 'historial']),
                    'url_label'    => 'Ver reporte',
                ],
                // Conteo org de pendientes de revisión: actualiza en vivo el badge
                // de la pestaña Historial y el banner.
                'pendientes_revision' => $this->generadoRepository->contarPendientesRevisionDeOrg($generado->organizacion_id),
            ]));
        } catch (Throwable $e) {
            Log::warning('ReporteNotificacionService: no se pudo emitir el evento en tiempo real', [
                'generado_id' => $generado->id,
                'user_id'     => $userId,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
