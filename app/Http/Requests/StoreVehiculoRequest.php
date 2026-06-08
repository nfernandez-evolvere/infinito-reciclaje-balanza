<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        // La unicidad es por organización (espeja el unique compuesto de la BD:
        // unique(organizacion_id, patente) y unique(organizacion_id, numero_interno)).
        // Sin el scope, un valor de otra organización en la tabla compartida dispararía
        // un falso "ya en uso".
        $orgId = app('organizacion')?->id;

        return [
            'patente'          => ['required', 'string', 'max:20', Rule::unique('vehiculos', 'patente')->where('organizacion_id', $orgId)],
            'numero_interno'   => ['nullable', 'string', 'max:20', Rule::unique('vehiculos', 'numero_interno')->where('organizacion_id', $orgId)],
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
