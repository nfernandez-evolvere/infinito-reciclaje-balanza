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
        'destinatarios'    => 'array',
        'opciones'         => 'array',
        'activo'           => 'boolean',
        'ultimo_envio_at'  => 'datetime',
        'proximo_envio_at' => 'datetime',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Formatos en los que se adjunta el reporte al email (informe_mensual).
     * Sanitiza contra los valores soportados y mantiene el orden canónico
     * (PDF, Excel). Si no hay nada configurado, vuelve a 'pdf' por defecto:
     * cubre los programados creados antes de esta opción y los de tipo alertas.
     *
     * @return list<string>
     */
    public function formatos(): array
    {
        $formatos = $this->opciones['formatos'] ?? [];

        return array_values(array_intersect(['pdf', 'excel'], $formatos)) ?: ['pdf'];
    }
}
