<?php

namespace App\Repositories;

use App\Models\ReporteConfiguracion;

class ReporteConfiguracionRepository
{
    public function first(): ?ReporteConfiguracion
    {
        return ReporteConfiguracion::first();
    }

    public function updateOrCreate(array $data): ReporteConfiguracion
    {
        return ReporteConfiguracion::updateOrCreate([], $data);
    }
}
