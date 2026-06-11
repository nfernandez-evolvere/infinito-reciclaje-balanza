<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConclusionesReporteGeneradoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'conclusiones' => ['nullable', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'conclusiones.max' => 'El análisis no puede superar los 20.000 caracteres.',
        ];
    }
}
