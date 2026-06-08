<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTipoServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        // La unicidad es por organización (espeja el unique compuesto de la BD:
        // unique(organizacion_id, nombre)). Sin el scope, el nombre de otra
        // organización en la tabla compartida dispararía un falso "ya en uso".
        return [
            'nombre'              => ['required', 'string', 'max:100', Rule::unique('tipos_servicio', 'nombre')->where('organizacion_id', app('organizacion')?->id)],
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
