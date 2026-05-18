<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonaServicioHorario extends Model
{
    protected $table = 'zona_servicio_horarios';

    public $timestamps = false;

    protected $fillable = [
        'zona_id',
        'tipo_servicio_id',
        'dia_semana',
        'franja',
        'hora_inicio',
        'hora_fin',
    ];
}
