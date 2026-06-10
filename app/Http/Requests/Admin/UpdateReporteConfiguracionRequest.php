<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReporteConfiguracionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'municipalidad_nombre'        => ['required', 'string', 'max:200'],
            'intro_empresa'               => ['nullable', 'string', 'max:2000'],
            'servicios'                   => ['nullable', 'array', 'max:6'],
            'servicios.*.titulo'          => ['required_with:servicios', 'string', 'max:100'],
            'servicios.*.descripcion'     => ['required_with:servicios', 'string', 'max:300'],
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
