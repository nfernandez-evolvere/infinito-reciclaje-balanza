<?php

namespace App\Http\Requests\Admin;

use App\Repositories\ReporteConfiguracionRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReporteProgramadoRequest extends FormRequest
{
    public function rules(): array
    {
        $config = app(ReporteConfiguracionRepository::class)->first();
        $tiposActivos = array_keys(array_filter([
            'informe_mensual' => $config->tipo_informe_mensual_activo ?? true,
            'alertas'         => $config->tipo_alertas_activo ?? false,
        ]));
        if (empty($tiposActivos)) {
            $tiposActivos = ['informe_mensual', 'alertas'];
        }

        return [
            'nombre'        => ['required', 'string', 'max:150'],
            'tipo'          => ['required', Rule::in($tiposActivos)],
            'frecuencia'    => ['required', 'in:diaria,semanal,quincenal,mensual'],
            'destinatarios' => ['required', 'string'],
            // El informe mensual elige sus formatos (al menos uno). Las alertas
            // se envían siempre en PDF, así que ahí el campo no es obligatorio.
            'formatos'   => [Rule::requiredIf($this->input('tipo') === 'informe_mensual'), 'array'],
            'formatos.*' => ['string', 'in:pdf,excel'],
            // Revisión antes del envío: 'heredar' sigue el default global de la
            // configuración; 'revisar'/'directo' lo sobreescriben por reporte.
            'revision' => ['nullable', 'in:heredar,revisar,directo'],
            'activo'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'formatos.required' => 'Elegí al menos un formato para el envío (PDF o Excel).',
            'formatos.*.in'     => 'El formato seleccionado no es válido.',
            'revision.in'       => 'La opción de revisión seleccionada no es válida.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['activo' => $this->boolean('activo', true)]);
    }
}
