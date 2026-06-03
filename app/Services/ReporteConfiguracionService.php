<?php

namespace App\Services;

use App\Models\ReporteConfiguracion;
use App\Repositories\ReporteConfiguracionRepository;

class ReporteConfiguracionService
{
    public function __construct(
        protected ReporteConfiguracionRepository $configuracionRepository,
    ) {}

    public function update(array $validated): ReporteConfiguracion
    {
        if (isset($validated['servicios'])) {
            $validated['servicios'] = array_values(array_filter(
                $validated['servicios'],
                fn ($s) => ! empty($s['titulo'])
            ));
        }

        // No sobreescribir la API key si viene vacía
        if (empty($validated['ai_api_key'])) {
            unset($validated['ai_api_key']);
        }

        return $this->configuracionRepository->updateOrCreate($validated);
    }
}
