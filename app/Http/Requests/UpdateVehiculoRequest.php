<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $vehiculoId = $this->route('vehiculo')?->id;

        return [
            'patente'          => ['required', 'string', 'max:20', Rule::unique('vehiculos', 'patente')->ignore($vehiculoId)],
            'numero_interno'   => ['required', 'string', 'max:20', Rule::unique('vehiculos', 'numero_interno')->ignore($vehiculoId)],
            'tara_kg'          => ['required', 'integer', 'min:1'],
            'tipo_vehiculo_id' => ['required', 'integer', 'exists:tipos_vehiculo,id'],
            'titular'          => ['required', 'string', 'max:200'],
            'capacidad_kg'     => ['nullable', 'integer', 'min:1'],
            'observaciones'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'patente'          => 'patente',
            'numero_interno'   => 'número interno',
            'tara_kg'          => 'tara',
            'tipo_vehiculo_id' => 'tipo de vehículo',
            'titular'          => 'titular',
            'capacidad_kg'     => 'capacidad',
            'observaciones'    => 'observaciones',
        ];
    }
}
