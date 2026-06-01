<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreReporteProgramadoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nombre'         => ['required', 'string', 'max:150'],
            'tipo'           => ['required', 'in:informe_mensual,alertas'],
            'frecuencia'     => ['required', 'in:mensual,semanal,custom'],
            'cron_expresion' => ['nullable', 'string', 'max:50'],
            'destinatarios'  => ['required', 'string'],
            'opciones'       => ['nullable', 'array'],
            'activo'         => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['activo' => $this->boolean('activo', true)]);
    }
}
