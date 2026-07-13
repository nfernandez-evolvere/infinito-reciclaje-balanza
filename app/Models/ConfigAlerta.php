<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperConfigAlerta
 */
class ConfigAlerta extends Model
{
    use BelongsToOrganizacion;

    protected $table = 'config_alertas';

    protected $fillable = [
        'organizacion_id',
        'tipo',
        'activo',
        'umbral_valor',
        'hora_inicio',
        'hora_fin',
    ];

    protected $casts = [
        'activo'       => 'boolean',
        'umbral_valor' => 'float',
    ];

    // Valores por defecto por tipo cuando no hay registro en DB
    public static function defaults(): array
    {
        return [
            'peso_fuera_rango' => [
                'activo'       => true,
                'umbral_valor' => null,
                'descripcion'  => 'Se genera al registrar un pesaje con peso bruto fuera del rango habitual del tipo de vehículo.',
                'umbral_label' => null,
            ],
            'volumen_diario_atipico' => [
                'activo'       => true,
                'umbral_valor' => 20.0,
                'descripcion'  => 'Se genera cuando el volumen diario total se desvía más de X% del promedio de los últimos 30 días.',
                'umbral_label' => 'Porcentaje de desviación del promedio',
            ],
            'gap_registro' => [
                'activo'       => true,
                'umbral_valor' => 120.0,
                'hora_inicio'  => '08:00',
                'hora_fin'     => '18:00',
                'descripcion'  => 'Se genera cuando no hay pesajes durante X minutos consecutivos dentro del horario operativo configurado abajo.',
                'umbral_label' => 'Minutos sin actividad',
            ],
            'frecuencia_zona_atipica' => [
                'activo'       => true,
                'umbral_valor' => 30.0,
                'descripcion'  => 'Se genera cuando la frecuencia de pesajes de una zona se desvía más de X% de su promedio histórico.',
                'umbral_label' => 'Porcentaje de desviación del promedio por zona',
            ],
            'vehiculo_no_habitual' => [
                'activo'       => true,
                'umbral_valor' => null,
                'descripcion'  => 'Se genera al registrar un pesaje con un tipo de vehículo que no coincide con los tipos habituales del servicio seleccionado.',
                'umbral_label' => null,
            ],
        ];
    }
}
