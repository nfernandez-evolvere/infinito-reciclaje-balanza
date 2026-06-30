<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperZonaTurno
 */
class ZonaTurno extends Model
{
    protected $table = 'zona_turnos';

    public $timestamps = false;

    protected $fillable = [
        'zona_id',
        'turno',
    ];
}
