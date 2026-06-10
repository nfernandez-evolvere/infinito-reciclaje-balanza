<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DescartarReporteGeneradoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'motivo' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.max' => 'El motivo no puede superar los 500 caracteres.',
        ];
    }
}
