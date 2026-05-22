<?php

namespace App\Repositories;

use App\Models\PesajeLog;
use Illuminate\Database\Eloquent\Collection;

class PesajeLogRepository
{
    public function create(array $data): PesajeLog
    {
        return PesajeLog::create($data);
    }

    public function porPesaje(int $pesajeId): Collection
    {
        return PesajeLog::with('usuario')
            ->where('pesaje_id', $pesajeId)
            ->orderByDesc('created_at')
            ->get();
    }
}
