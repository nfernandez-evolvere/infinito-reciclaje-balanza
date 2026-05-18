<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    protected $table = 'zonas';

    protected $fillable = [
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
