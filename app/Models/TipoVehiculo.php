<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVehiculo extends Model
{
    use HasFactory, BelongsToOrganizacion;

    protected $table = 'tipos_vehiculo';

    protected $fillable = [
        'organizacion_id',
        'nombre',
        'peso_min_kg',
        'peso_max_kg',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
