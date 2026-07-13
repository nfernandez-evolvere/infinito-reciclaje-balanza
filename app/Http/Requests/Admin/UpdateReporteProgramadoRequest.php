<?php

namespace App\Http\Requests\Admin;

class UpdateReporteProgramadoRequest extends StoreReporteProgramadoRequest
{
    /**
     * El modal de edición ya no expone el switch de "activo" (se controla
     * desde el menú de acciones de la tabla) — a diferencia de Store, acá se
     * preserva el estado actual del programado en vez de aplicar el default
     * `true` de creación.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo'                   => $this->boolean('activo', $this->route('programado')->activo),
            'secciones_personalizadas' => $this->boolean('secciones_personalizadas'),
        ]);
    }
}
