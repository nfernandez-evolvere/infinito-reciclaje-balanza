<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperReporteDestinatario
 */
class ReporteDestinatario extends Model
{
    use BelongsToOrganizacion;

    protected $table = 'reporte_destinatarios';

    protected $fillable = [
        'organizacion_id',
        'email',
        'nombre',
        'uso_count',
    ];
}
