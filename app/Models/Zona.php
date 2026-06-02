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
    use HasFactory, BelongsToOrganizacion;

    protected $table = 'zonas';

    protected $fillable = [
        'organizacion_id',
        'nombre',
        'hectareas',
        'barrios',
        'habitantes',
        'activo',
    ];

    protected $casts = [
        'hectareas' => 'float',
        'activo'    => 'boolean',
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
