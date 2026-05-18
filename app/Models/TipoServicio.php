<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipoServicio extends Model
{
    use HasFactory;

    protected $table = 'tipos_servicio';

    protected $fillable = [
        'nombre',
        'tipo_vehiculo_sugerido_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tipoVehiculo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_sugerido_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
