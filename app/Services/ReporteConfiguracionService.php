<?php

namespace App\Services;

use App\Models\ReporteConfiguracion;
use App\Repositories\ReporteConfiguracionRepository;
use App\Support\ReporteSecciones;

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

        // Con todas las secciones activas se persiste null: así los defaults
        // siguen la evolución del catálogo sin re-guardar la configuración.
        if (array_key_exists('secciones', $validated)) {
            $validated['secciones'] = ReporteSecciones::esTodo($validated['secciones'])
                ? null
                : ReporteSecciones::sanitizar($validated['secciones']);
        }

        // No sobreescribir la API key si viene vacía
        if (empty($validated['ai_api_key'])) {
            unset($validated['ai_api_key']);
        }

        return $this->configuracionRepository->updateOrCreate($validated);
    }
}
