<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin \Eloquent
 * @mixin IdeHelperVehiculoLog
 */
class VehiculoLog extends Model
{
    protected $table = 'vehiculos_log';

    protected $fillable = [
        'vehiculo_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'motivo',
        'usuario_id',
    ];

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
