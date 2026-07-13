<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $organizacion_id
 * @property int|null $user_id
 * @property string $tipo
 * @property string $titulo
 * @property string|null $descripcion
 * @property int|null $pesaje_id
 * @property int|null $zona_id
 * @property int|null $reporte_generado_id
 * @property Pesaje|null $pesaje
 * @property Zona|null $zona
 * @property ReporteGenerado|null $reporteGenerado
 * @property Carbon $fecha_deteccion
 * @property bool $leida
 * @property Carbon|null $leida_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @mixin IdeHelperAlerta
 */
class Alerta extends Model
{
    use BelongsToOrganizacion;

    // Tipos de notificación de reportes (no son anomalías de pesaje: comparten
    // la tabla alertas para reusar campana, endpoints y "marcar leídas").
    public const TIPO_REPORTE_REVISION = 'reporte_revision';

    public const TIPO_REPORTE_ENVIADO = 'reporte_enviado';

    public const TIPO_REPORTE_FALLIDO = 'reporte_fallido';

    protected $table = 'alertas';

    protected $fillable = [
        'organizacion_id',
        'user_id',
        'uuid',
        'tipo',
        'titulo',
        'descripcion',
        'pesaje_id',
        'zona_id',
        'reporte_generado_id',
        'fecha_deteccion',
        'leida',
        'leida_at',
        'leida_por_id',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $casts = [
        'leida'               => 'boolean',
        'leida_at'            => 'datetime',
        'fecha_deteccion'     => 'date',
        'pesaje_id'           => 'integer',
        'zona_id'             => 'integer',
        'reporte_generado_id' => 'integer',
        'user_id'             => 'integer',
        'organizacion_id'     => 'integer',
    ];

    public function pesaje(): BelongsTo
    {
        return $this->belongsTo(Pesaje::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function reporteGenerado(): BelongsTo
    {
        return $this->belongsTo(ReporteGenerado::class);
    }

    public function leidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leida_por_id');
    }

    public function tipoLabel(): string
    {
        return match ($this->tipo) {
            'peso_fuera_rango'          => 'Peso fuera de rango',
            'volumen_diario_atipico'    => 'Volumen atípico',
            'gap_registro'              => 'Sin actividad',
            'frecuencia_zona_atipica'   => 'Frecuencia atípica',
            'vehiculo_no_habitual'      => 'Vehículo no habitual',
            self::TIPO_REPORTE_REVISION => 'Reporte para revisar',
            self::TIPO_REPORTE_ENVIADO  => 'Reporte enviado',
            self::TIPO_REPORTE_FALLIDO  => 'Reporte con error',
            default                     => $this->tipo,
        };
    }

    public function tipoVariant(): string
    {
        return match ($this->tipo) {
            'peso_fuera_rango'          => 'warning',
            'volumen_diario_atipico'    => 'destructive',
            'gap_registro'              => 'secondary',
            'frecuencia_zona_atipica'   => 'warning',
            'vehiculo_no_habitual'      => 'warning',
            self::TIPO_REPORTE_REVISION => 'warning',
            self::TIPO_REPORTE_ENVIADO  => 'success',
            self::TIPO_REPORTE_FALLIDO  => 'destructive',
            default                     => 'default',
        };
    }

    public function tipoIcono(): string
    {
        return match ($this->tipo) {
            'peso_fuera_rango'          => 'scale',
            'volumen_diario_atipico'    => 'trending-up',
            'gap_registro'              => 'clock',
            'frecuencia_zona_atipica'   => 'map-pin',
            'vehiculo_no_habitual'      => 'truck',
            self::TIPO_REPORTE_REVISION => 'file-clock',
            self::TIPO_REPORTE_ENVIADO  => 'file-check',
            self::TIPO_REPORTE_FALLIDO  => 'file-x',
            default                     => 'triangle-alert',
        };
    }
}
