<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperZonaServicio
 */
class ZonaServicio extends Model
{
    protected $table = 'zona_servicios';

    protected $fillable = [
        'zona_id',
        'tipo_servicio_id',
    ];

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(TipoServicio::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(ZonaServicioTurno::class, 'zona_id', 'zona_id')
            ->where('tipo_servicio_id', $this->tipo_servicio_id);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(ZonaServicioHorario::class, 'zona_id', 'zona_id')
            ->where('tipo_servicio_id', $this->tipo_servicio_id)
            ->orderBy('dia_semana')
            ->orderBy('franja');
    }

    /** Returns ['Diurna', 'Nocturna'] format for Alpine */
    public function getTurnosArrayAttribute(): array
    {
        return $this->turnos->pluck('turno')->toArray();
    }

    /** Returns 7-element array of franja arrays for Alpine's horariosPorDia */
    public function getHorariosPorDiaAttribute(): array
    {
        $result = array_fill(0, 7, []);
        foreach ($this->horarios as $h) {
            $result[$h->dia_semana - 1][] = [
                'inicio' => substr($h->hora_inicio, 0, 5),
                'fin'    => substr($h->hora_fin, 0, 5),
            ];
        }
        return $result;
    }
}
