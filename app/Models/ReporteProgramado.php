<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperReporteProgramado
 */
class ReporteProgramado extends Model
{
    use BelongsToOrganizacion;

    protected $table = 'reportes_programados';

    protected $fillable = [
        'organizacion_id',
        'tipo',
        'nombre',
        'frecuencia',
        'cron_expresion',
        'destinatarios',
        'opciones',
        'activo',
        'ultimo_envio_at',
        'proximo_envio_at',
    ];

    protected $casts = [
        'destinatarios'   => 'array',
        'opciones'        => 'array',
        'activo'          => 'boolean',
        'ultimo_envio_at' => 'datetime',
        'proximo_envio_at'=> 'datetime',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
