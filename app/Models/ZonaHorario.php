<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperZonaHorario
 */
class ZonaHorario extends Model
{
    protected $table = 'zona_horarios';

    public $timestamps = false;

    protected $fillable = [
        'zona_id',
        'dia_semana',
        'franja',
        'hora_inicio',
        'hora_fin',
    ];
}
