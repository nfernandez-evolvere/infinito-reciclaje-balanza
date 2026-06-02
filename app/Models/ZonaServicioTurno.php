<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperZonaServicioTurno
 */
class ZonaServicioTurno extends Model
{
    protected $table = 'zona_servicio_turnos';

    public $timestamps = false;

    protected $fillable = [
        'zona_id',
        'tipo_servicio_id',
        'turno',
    ];
}
