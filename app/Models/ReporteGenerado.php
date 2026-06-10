<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro inmutable de cada reporte producido: una descarga manual (Excel/PDF)
 * o un envío programado. Guarda los metadatos (período, filtros, formato,
 * destinatarios) y un `snapshot` con los datos congelados tal como se generó:
 * así una re-descarga reproduce el reporte idéntico al original, sin recalcular
 * sobre los pesajes vivos (la tara de un vehículo puede cambiar después).
 * Las entradas previas a la introducción del snapshot lo tienen en null y caen
 * al recálculo bajo demanda.
 *
 * @mixin \Eloquent
 * @mixin IdeHelperReporteGenerado
 */
class ReporteGenerado extends Model
{
    use BelongsToOrganizacion;

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
    ];

    protected $casts = [
        'periodo_desde' => 'date',
        'periodo_hasta' => 'date',
        'filtros'       => 'array',
        'destinatarios' => 'array',
        'snapshot'      => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
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
