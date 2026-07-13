<?php

namespace App\Http\Requests\Admin;

use App\Support\ReporteSecciones;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportReporteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
            // Ajuste ad-hoc del popover de la pantalla Generar: lista de secciones
            // habilitadas para el formato que se descarga. Ausente → configuración
            // general. Se validan contra ambos catálogos porque el mismo request
            // sirve al export PDF y al Excel; el controller sanea por formato.
            'secciones'   => ['nullable', 'array'],
            'secciones.*' => ['string', Rule::in([...ReporteSecciones::pdfKeys(), ...ReporteSecciones::excelKeys()])],
        ];
    }
}
