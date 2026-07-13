<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use App\Support\ReporteSecciones;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperReporteConfiguracion
 */
class ReporteConfiguracion extends Model
{
    use BelongsToOrganizacion;

    protected $table = 'reporte_configuraciones';

    /**
     * La revisión de envíos arranca activada: espeja el default de la columna
     * para las instancias en memoria (la vista de configuración usa
     * `new ReporteConfiguracion` cuando la organización aún no guardó nada).
     */
    protected $attributes = [
        'revision_requerida' => true,
    ];

    protected $fillable = [
        'organizacion_id',
        'municipalidad_nombre',
        'intro_empresa',
        'servicios',
        'secciones',
        'ai_enabled',
        'ai_proveedor',
        'ai_api_key',
        'ai_modelo',
        'ai_prompt',
        'tipo_informe_mensual_activo',
        'tipo_alertas_activo',
        'revision_requerida',
    ];

    protected $casts = [
        'servicios'                   => 'array',
        'secciones'                   => 'array',
        'ai_enabled'                  => 'boolean',
        'ai_api_key'                  => 'encrypted',
        'tipo_informe_mensual_activo' => 'boolean',
        'tipo_alertas_activo'         => 'boolean',
        'revision_requerida'          => 'boolean',
    ];

    protected $hidden = ['ai_api_key'];

    /**
     * Secciones default de la organización, saneadas contra el catálogo.
     * Null en la columna (o instancia sin guardar) = todas las secciones.
     *
     * @return array{pdf: list<string>, excel: list<string>}
     */
    public function secciones(): array
    {
        return ReporteSecciones::sanitizar($this->secciones ?? null);
    }

    /** @return list<string> */
    public function seccionesPdf(): array
    {
        return $this->secciones()['pdf'];
    }

    /** @return list<string> */
    public function seccionesExcel(): array
    {
        return $this->secciones()['excel'];
    }
}
