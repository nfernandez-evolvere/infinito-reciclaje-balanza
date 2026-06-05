<?php

namespace App\Repositories;

use App\Models\ReporteGenerado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReporteGeneradoRepository
{
    public function create(array $data): ReporteGenerado
    {
        return ReporteGenerado::create($data);
    }

    /**
     * Historial paginado de la organización activa (más reciente primero).
     * El scope de organización lo aplica el trait BelongsToOrganizacion.
     */
    public function paginarHistorial(int $porPagina = 15): LengthAwarePaginator
    {
        return ReporteGenerado::with('usuario:id,name')
            ->latest()
            ->paginate($porPagina);
    }
}
