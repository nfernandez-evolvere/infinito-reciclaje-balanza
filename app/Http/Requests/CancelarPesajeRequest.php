<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelarPesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'El motivo de cancelación es obligatorio.',
            'motivo.min'      => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max'      => 'El motivo no puede superar los 500 caracteres.',
        ];
    }
}
