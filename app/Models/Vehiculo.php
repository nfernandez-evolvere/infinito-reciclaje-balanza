<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
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

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
