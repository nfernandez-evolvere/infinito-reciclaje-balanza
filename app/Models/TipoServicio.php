<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoServicio extends Model
{
    use HasFactory, BelongsToOrganizacion;

    protected $table = 'tipos_servicio';

    protected $fillable = [
        'organizacion_id',
        'nombre',
        'tipo_vehiculo_sugerido_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tipoVehiculoSugerido(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_sugerido_id');
    }

    public function tiposVehiculo(): BelongsToMany
    {
        return $this->belongsToMany(TipoVehiculo::class, 'tipo_servicio_tipo_vehiculo');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
