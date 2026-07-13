<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganizacion;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperTipoVehiculo
 */
class TipoVehiculo extends Model
{
    use BelongsToOrganizacion, HasFactory;

    /**
     * Factor sobre el peso máximo habitual a partir del cual un peso bruto
     * se considera un error de carga y se bloquea (no una simple alerta).
     * Ej.: peso_max 30.000 kg → tope duro 60.000 kg.
     */
    public const FACTOR_TOPE_PESO = 2;

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

    /**
     * Tope duro de peso bruto: por encima de este valor la carga se bloquea.
     * Es null cuando el tipo no tiene peso máximo definido.
     */
    protected function pesoTopeKg(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->peso_max_kg !== null
                ? (int) $this->peso_max_kg * self::FACTOR_TOPE_PESO
                : null,
        );
    }
}
