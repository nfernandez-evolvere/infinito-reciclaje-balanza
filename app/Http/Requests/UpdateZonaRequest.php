<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateZonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'     => ['required', 'string', 'max:150', Rule::unique('zonas')->ignore($this->route('zona'))],
            'hectareas'  => ['nullable', 'numeric', 'min:0'],
            'barrios'    => ['nullable', 'integer', 'min:0'],
            'habitantes' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'     => 'nombre',
            'hectareas'  => 'hectáreas',
            'barrios'    => 'cantidad de barrios',
            'habitantes' => 'cantidad de habitantes',
        ];
    }
}
