<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int         $id
 * @property string      $uuid
 * @property int         $organizacion_id
 * @property int|null    $user_id
 * @property string      $tipo
 * @property string      $titulo
 * @property string|null $descripcion
 * @property int|null    $pesaje_id
 * @property int|null    $zona_id
 * @property \Carbon\Carbon $fecha_deteccion
 * @property bool        $leida
 * @property \Carbon\Carbon|null $leida_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Alerta extends Model
{
    use BelongsToOrganizacion;

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
        'leida'           => 'boolean',
        'leida_at'        => 'datetime',
        'fecha_deteccion' => 'date',
        'pesaje_id'       => 'integer',
        'zona_id'         => 'integer',
        'user_id'         => 'integer',
        'organizacion_id' => 'integer',
    ];

    public function pesaje(): BelongsTo
    {
        return $this->belongsTo(Pesaje::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function leidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leida_por_id');
    }

    public function tipoLabel(): string
    {
        return match ($this->tipo) {
            'peso_fuera_rango'        => 'Peso fuera de rango',
            'volumen_diario_atipico'  => 'Volumen atípico',
            'gap_registro'            => 'Sin actividad',
            'frecuencia_zona_atipica' => 'Frecuencia atípica',
            default                   => $this->tipo,
        };
    }

    public function tipoVariant(): string
    {
        return match ($this->tipo) {
            'peso_fuera_rango'        => 'warning',
            'volumen_diario_atipico'  => 'destructive',
            'gap_registro'            => 'secondary',
            'frecuencia_zona_atipica' => 'warning',
            default                   => 'default',
        };
    }

    public function tipoIcono(): string
    {
        return match ($this->tipo) {
            'peso_fuera_rango'        => 'scale',
            'volumen_diario_atipico'  => 'trending-up',
            'gap_registro'            => 'clock',
            'frecuencia_zona_atipica' => 'map-pin',
            default                   => 'triangle-alert',
        };
    }
}
