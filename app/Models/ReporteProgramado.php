<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use App\Support\ReporteSecciones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'creado_por_id',
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

    /** Usuario que creó el programado: destinatario de las notificaciones de sus envíos. */
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_id');
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

    /**
     * Opción de revisión propia del programado, saneada contra los valores
     * soportados. 'heredar' cubre los programados creados antes de esta opción.
     */
    public function revisionOpcion(): string
    {
        $opcion = $this->opciones['revision'] ?? 'heredar';

        return in_array($opcion, ['heredar', 'revisar', 'directo'], true) ? $opcion : 'heredar';
    }

    /**
     * True si este programado personalizó sus secciones (opciones['secciones']);
     * false = hereda las de la configuración general.
     */
    public function seccionesPersonalizadas(): bool
    {
        return is_array($this->opciones['secciones'] ?? null);
    }

    /**
     * Secciones del informe de este programado, resolviendo la cascada igual
     * que requiereRevision(): la personalización propia (opciones['secciones'])
     * sobreescribe el default de la organización; sin personalizar cae a la
     * configuración general, y sin configuración creada, a todas las secciones.
     *
     * @return array{pdf: list<string>, excel: list<string>}
     */
    public function secciones(?ReporteConfiguracion $config): array
    {
        if ($this->seccionesPersonalizadas()) {
            return ReporteSecciones::sanitizar($this->opciones['secciones']);
        }

        return $config?->secciones() ?? ReporteSecciones::sanitizar(null);
    }

    /**
     * Resuelve si el envío de este programado queda pendiente de revisión:
     * la opción propia ('revisar'/'directo') sobreescribe el default global
     * de la organización; 'heredar' cae a config.revision_requerida. Sin
     * configuración creada también se revisa: ningún envío sale sin
     * aprobación salvo decisión explícita.
     */
    public function requiereRevision(?ReporteConfiguracion $config): bool
    {
        return match ($this->revisionOpcion()) {
            'revisar' => true,
            'directo' => false,
            default   => (bool) ($config->revision_requerida ?? true),
        };
    }
}
