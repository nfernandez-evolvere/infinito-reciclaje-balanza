<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre'              => ['required', 'string', 'max:100', 'unique:tipos_servicio,nombre'],
            'tipo_vehiculo_ids'   => ['nullable', 'array'],
            'tipo_vehiculo_ids.*' => ['integer', 'exists:tipos_vehiculo,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'            => 'nombre',
            'tipo_vehiculo_ids' => 'vehículos sugeridos',
        ];
    }
}
