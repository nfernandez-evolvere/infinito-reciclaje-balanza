<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de cada reporte producido: una descarga manual (Excel/PDF) o un
 * envío programado. Guarda los metadatos (período, filtros, formato,
 * destinatarios congelados) y un `snapshot` con los datos tal como se generó:
 * así una re-descarga o un envío aprobado reproduce el reporte idéntico al
 * original, sin recalcular sobre los pesajes vivos (la tara de un vehículo
 * puede cambiar después). Las entradas previas a la introducción del snapshot
 * lo tienen en null y caen al recálculo bajo demanda.
 *
 * Estados del flujo programado: generando → en_revision|enviando → enviado;
 * en_revision → descartado; cualquier fase puede caer a fallido y reintentarse.
 * Las descargas manuales usan el estado legacy `generado`.
 *
 * @mixin \Eloquent
 * @mixin IdeHelperReporteGenerado
 */
class ReporteGenerado extends Model
{
    use BelongsToOrganizacion;

    public const ESTADO_GENERANDO = 'generando';

    public const ESTADO_EN_REVISION = 'en_revision';

    public const ESTADO_ENVIANDO = 'enviando';

    public const ESTADO_ENVIADO = 'enviado';

    public const ESTADO_FALLIDO = 'fallido';

    public const ESTADO_DESCARTADO = 'descartado';

    public const ESTADO_GENERADO = 'generado';

    protected $table = 'reportes_generados';

    protected $fillable = [
        'organizacion_id',
        'usuario_id',
        'reporte_programado_id',
        'origen',
        'tipo',
        'formato',
        'periodo_desde',
        'periodo_hasta',
        'filtros',
        'destinatarios',
        'estado',
        'error',
        'conclusiones',
        'snapshot',
        'revisado_por_id',
        'revisado_at',
        'enviado_at',
        'motivo_descarte',
    ];

    protected $casts = [
        'periodo_desde' => 'date',
        'periodo_hasta' => 'date',
        'filtros'       => 'array',
        'destinatarios' => 'array',
        'snapshot'      => 'array',
        'revisado_at'   => 'datetime',
        'enviado_at'    => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_id');
    }

    public function programado(): BelongsTo
    {
        return $this->belongsTo(ReporteProgramado::class, 'reporte_programado_id');
    }

    /**
     * Filtros saneados contra las columnas reales, listos para volver a generar.
     *
     * @return array<string, int>
     */
    public function filtrosNormalizados(): array
    {
        return array_filter(array_intersect_key(
            $this->filtros ?? [],
            array_flip(['zona_id', 'tipo_servicio_id', 'tipo_vehiculo_id'])
        ));
    }
}
