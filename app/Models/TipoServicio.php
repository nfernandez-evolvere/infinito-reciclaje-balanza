<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperTipoServicio
 */
class TipoServicio extends Model
{
    use BelongsToOrganizacion, HasFactory;

    protected $table = 'tipos_servicio';

    protected $fillable = [
        'organizacion_id',
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tiposVehiculo(): BelongsToMany
    {
        return $this->belongsToMany(TipoVehiculo::class, 'tipo_servicio_tipo_vehiculo');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
