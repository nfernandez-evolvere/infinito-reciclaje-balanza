<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizacion extends Model
{
    protected $fillable = ['nombre', 'slug', 'activo'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tiposVehiculo(): HasMany
    {
        return $this->hasMany(TipoVehiculo::class);
    }

    public function tiposServicio(): HasMany
    {
        return $this->hasMany(TipoServicio::class);
    }

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function zonas(): HasMany
    {
        return $this->hasMany(Zona::class);
    }
}
