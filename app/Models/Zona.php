<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'hectareas'  => 'float',
        'activo'     => 'boolean',
        'centro_lat' => 'float',
        'centro_lng' => 'float',
    ];

    public function zonaServicios(): HasMany
    {
        return $this->hasMany(ZonaServicio::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
