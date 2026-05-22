<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesaje extends Model
{
    use HasFactory;

    protected $table = 'pesajes';

    protected $fillable = [
        'vehiculo_id',
        'operador_id',
        'tipo_servicio_id',
        'zona_id',
        'turno',
        'peso_bruto_kg',
        'peso_tara_kg',
        'peso_neto_kg',
        'alerta_peso',
        'observaciones',
        'estado',
        'hora_salida',
        'bruto_salida_kg',
        'editado',
    ];

    protected $casts = [
        'alerta_peso'   => 'boolean',
        'editado'       => 'boolean',
        'hora_salida'   => 'datetime',
        'peso_bruto_kg' => 'integer',
        'peso_tara_kg'  => 'integer',
        'peso_neto_kg'  => 'integer',
    ];

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(TipoServicio::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PesajeLog::class);
    }

    public function scopeDelTurno($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeEnPredio($query)
    {
        return $query->where('estado', 'En predio');
    }

    public function estaEnPredio(): bool
    {
        return $this->estado === 'En predio';
    }

    public function estaCerrado(): bool
    {
        return $this->estado === 'Cerrado';
    }
}
