<?php

namespace App\Repositories;

use App\Models\VehiculoLog;
use Illuminate\Database\Eloquent\Collection;

class VehiculoLogRepository
{
    public function create(array $data): VehiculoLog
    {
        return VehiculoLog::create($data);
    }

    public function porVehiculo(int $vehiculoId): Collection
    {
        return VehiculoLog::with('usuario')
            ->where('vehiculo_id', $vehiculoId)
            ->orderByDesc('created_at')
            ->get();
    }
}
