<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesajeLog extends Model
{
    protected $table = 'pesajes_log';

    protected $fillable = [
        'pesaje_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'motivo',
        'usuario_id',
    ];

    public function pesaje(): BelongsTo
    {
        return $this->belongsTo(Pesaje::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
