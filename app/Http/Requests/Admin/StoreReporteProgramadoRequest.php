<?php

namespace App\Http\Requests\Admin;

use App\Repositories\ReporteConfiguracionRepository;
use App\Support\ReporteSecciones;
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
            'nombre'     => ['required', 'string', 'max:150'],
            'tipo'       => ['required', Rule::in($tiposActivos)],
            'frecuencia' => ['required', 'in:diaria,semanal,quincenal,mensual'],
            // Fecha del primer envío (hoy o futura): ancla el cronograma — elegir
            // el 1 hace que corra todos los 1 cubriendo el mes anterior completo.
            'inicio_en'     => ['required', 'date', 'after_or_equal:today'],
            'destinatarios' => ['required', 'string'],
            // El informe mensual elige sus formatos (al menos uno). Las alertas
            // se envían siempre en PDF, así que ahí el campo no es obligatorio.
            'formatos'   => [Rule::requiredIf($this->input('tipo') === 'informe_mensual'), 'array'],
            'formatos.*' => ['string', 'in:pdf,excel'],
            // Revisión antes del envío: 'heredar' sigue el default global de la
            // configuración; 'revisar'/'directo' lo sobreescriben por reporte.
            'revision' => ['nullable', 'in:heredar,revisar,directo'],
            // Secciones del informe: sin personalizar hereda la configuración
            // general. Personalizando, el PDF puede quedar sin páginas de contenido
            // (portada y cierre son fijas) pero el Excel necesita al menos una hoja.
            'secciones_personalizadas' => ['boolean'],
            'secciones'                => ['nullable', 'array'],
            'secciones.pdf'            => ['nullable', 'array'],
            'secciones.pdf.*'          => ['string', Rule::in(ReporteSecciones::pdfKeys())],
            'secciones.excel'          => [
                Rule::requiredIf($this->requiereSeccionesExcel()),
                'nullable', 'array', 'min:1',
            ],
            'secciones.excel.*' => ['string', Rule::in(ReporteSecciones::excelKeys())],
            'activo'            => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'inicio_en.required'       => 'Elegí desde cuándo se envía el reporte.',
            'inicio_en.date'           => 'La fecha del primer envío no es válida.',
            'inicio_en.after_or_equal' => 'La fecha del primer envío no puede ser anterior a hoy.',
            'formatos.required'        => 'Elegí al menos un formato para el envío (PDF o Excel).',
            'formatos.*.in'            => 'El formato seleccionado no es válido.',
            'revision.in'              => 'La opción de revisión seleccionada no es válida.',
            'secciones.excel.required' => 'Elegí al menos una hoja para el Excel.',
            'secciones.excel.min'      => 'Elegí al menos una hoja para el Excel.',
            'secciones.pdf.*.in'       => 'Alguna de las secciones seleccionadas no es válida.',
            'secciones.excel.*.in'     => 'Alguna de las hojas seleccionadas no es válida.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo'                   => $this->boolean('activo', true),
            'secciones_personalizadas' => $this->boolean('secciones_personalizadas'),
        ]);
    }

    /** El Excel no puede quedar sin hojas: aplica al personalizar con formato excel. */
    private function requiereSeccionesExcel(): bool
    {
        return $this->boolean('secciones_personalizadas')
            && $this->input('tipo') === 'informe_mensual'
            && in_array('excel', (array) $this->input('formatos', []), true);
    }
}
