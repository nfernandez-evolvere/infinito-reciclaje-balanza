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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'geojson'    => filled($this->input('geojson')) ? $this->input('geojson') : null,
            'centro_lat' => filled($this->input('centro_lat')) ? $this->input('centro_lat') : null,
            'centro_lng' => filled($this->input('centro_lng')) ? $this->input('centro_lng') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:150',
                // La unicidad es por organización (espeja el unique compuesto de la BD:
                // unique(organizacion_id, nombre)). Sin el scope, el nombre de otra
                // organización en la tabla compartida dispararía un falso "ya en uso"
                // al editar (p. ej. cambiando sólo el contorno del mapa, no el nombre).
                Rule::unique('zonas', 'nombre')
                    ->where('organizacion_id', app('organizacion')?->id)
                    ->ignore($this->route('zona')),
            ],
            'hectareas'  => ['nullable', 'numeric', 'min:0'],
            'barrios'    => ['nullable', 'integer', 'min:0'],
            'habitantes' => ['nullable', 'integer', 'min:0'],
            'geojson'    => ['nullable', 'json'],
            'centro_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'centro_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'     => 'nombre',
            'hectareas'  => 'hectáreas',
            'barrios'    => 'cantidad de barrios',
            'habitantes' => 'cantidad de habitantes',
            'geojson'    => 'área en el mapa',
            'centro_lat' => 'latitud del centro',
            'centro_lng' => 'longitud del centro',
        ];
    }
}
