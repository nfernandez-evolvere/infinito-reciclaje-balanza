<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre'      => ['required', 'string', 'max:100'],
            'peso_min_kg' => ['required', 'integer', 'min:0'],
            'peso_max_kg' => ['required', 'integer', 'gt:peso_min_kg'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'      => 'nombre',
            'peso_min_kg' => 'peso mínimo',
            'peso_max_kg' => 'peso máximo',
        ];
    }

    public function messages(): array
    {
        return [
            'peso_max_kg.gt' => 'El peso máximo debe ser mayor al peso mínimo.',
        ];
    }
}
