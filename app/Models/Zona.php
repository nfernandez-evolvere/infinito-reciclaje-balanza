<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperZona
 */
class Zona extends Model
{
    use BelongsToOrganizacion, HasFactory;

    protected $table = 'zonas';

    protected $fillable = [
        'organizacion_id',
        'tipo_servicio_id',
        'nombre',
        'hectareas',
        'barrios',
        'habitantes',
        'activo',
        'geojson',
        'centro_lat',
        'centro_lng',
    ];

    protected $casts = [
        'tipo_servicio_id' => 'integer',
        'hectareas'        => 'float',
        'activo'           => 'boolean',
        'centro_lat'       => 'float',
        'centro_lng'       => 'float',
    ];

    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(TipoServicio::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(ZonaTurno::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(ZonaHorario::class)
            ->orderBy('dia_semana')
            ->orderBy('franja');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
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
