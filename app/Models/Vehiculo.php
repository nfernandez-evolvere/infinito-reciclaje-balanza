<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperVehiculo
 */
class Vehiculo extends Model
{
    use BelongsToOrganizacion, HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'organizacion_id',
        'patente',
        'numero_interno',
        'tara_kg',
        'tipo_vehiculo_id',
        'titular',
        'capacidad_kg',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'activo'       => 'boolean',
        'tara_kg'      => 'integer',
        'capacidad_kg' => 'integer',
    ];

    public function tipoVehiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class);
    }

    public function pesajes(): HasMany
    {
        return $this->hasMany(Pesaje::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VehiculoLog::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
