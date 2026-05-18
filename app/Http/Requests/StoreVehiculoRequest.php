<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'patente'          => ['required', 'string', 'max:20', 'unique:vehiculos,patente'],
            'numero_interno'   => ['required', 'string', 'max:20', 'unique:vehiculos,numero_interno'],
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
