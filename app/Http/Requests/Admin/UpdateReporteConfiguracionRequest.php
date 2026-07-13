<?php

namespace App\Http\Requests\Admin;

use App\Support\ReporteSecciones;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReporteConfiguracionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'municipalidad_nombre'    => ['required', 'string', 'max:200'],
            'intro_empresa'           => ['nullable', 'string', 'max:2000'],
            'servicios'               => ['nullable', 'array', 'max:6'],
            'servicios.*.titulo'      => ['required_with:servicios', 'string', 'max:100'],
            'servicios.*.descripcion' => ['required_with:servicios', 'string', 'max:300'],
            // Secciones default del informe v2. El PDF admite quedar sin páginas de
            // contenido (portada y cierre son fijas); el Excel exige al menos una hoja.
            // required_with: el form siempre manda alguna sección tildada; si el
            // request no trae ninguna (form viejo o todo destildado) se cae a null = todas.
            'secciones'                   => ['nullable', 'array'],
            'secciones.pdf'               => ['nullable', 'array'],
            'secciones.pdf.*'             => ['string', Rule::in(ReporteSecciones::pdfKeys())],
            'secciones.excel'             => ['required_with:secciones', 'array', 'min:1'],
            'secciones.excel.*'           => ['string', Rule::in(ReporteSecciones::excelKeys())],
            'ai_enabled'                  => ['boolean'],
            'ai_proveedor'                => ['nullable', 'string', 'max:50'],
            'ai_api_key'                  => ['nullable', 'string', 'max:500'],
            'ai_modelo'                   => ['nullable', 'string', 'max:100'],
            'ai_prompt'                   => ['nullable', 'string'],
            'tipo_informe_mensual_activo' => ['boolean'],
            'tipo_alertas_activo'         => ['boolean'],
            'revision_requerida'          => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'secciones.excel.required' => 'Elegí al menos una hoja para el Excel.',
            'secciones.excel.min'      => 'Elegí al menos una hoja para el Excel.',
            'secciones.pdf.*.in'       => 'Alguna de las secciones seleccionadas no es válida.',
            'secciones.excel.*.in'     => 'Alguna de las hojas seleccionadas no es válida.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ai_enabled'                  => $this->boolean('ai_enabled'),
            'tipo_informe_mensual_activo' => $this->boolean('tipo_informe_mensual_activo'),
            'tipo_alertas_activo'         => $this->boolean('tipo_alertas_activo'),
            'revision_requerida'          => $this->boolean('revision_requerida'),
            'servicios'                   => array_values(array_filter(
                $this->input('servicios', []),
                fn ($s) => ! empty($s['titulo'])
            )),
        ]);
    }
}
