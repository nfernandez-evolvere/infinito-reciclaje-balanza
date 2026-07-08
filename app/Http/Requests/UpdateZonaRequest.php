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
            'tipo_servicio_id' => ['required', 'integer', 'exists:tipos_servicio,id'],
            'nombre'           => [
                'required', 'string', 'max:150',
                // Único dentro del servicio, ignorando la propia zona al editar (p. ej.
                // cuando sólo se cambia el contorno del mapa y no el nombre).
                Rule::unique('zonas', 'nombre')
                    ->where('tipo_servicio_id', $this->input('tipo_servicio_id'))
                    ->ignore($this->route('zona')),
            ],
            'hectareas'  => ['nullable', 'numeric', 'min:0'],
            'barrios'    => ['nullable', 'integer', 'min:0'],
            'habitantes' => ['nullable', 'integer', 'min:0'],
            'geojson'    => ['nullable', 'json'],
            'centro_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'centro_lng' => ['nullable', 'numeric', 'between:-180,180'],
            // Turnos de texto libre por zona (sin catálogo): ver StoreZonaRequest.
            'turnos'              => ['nullable', 'array'],
            'turnos.*'            => ['required', 'string', 'max:20', 'distinct'],
            'horarios'            => ['nullable', 'array'],
            'horarios.*'          => ['nullable', 'array'],
            'horarios.*.*'        => ['nullable', 'array'],
            'horarios.*.*.inicio' => ['nullable', 'string', 'date_format:H:i'],
            'horarios.*.*.fin'    => ['nullable', 'string', 'date_format:H:i'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo_servicio_id' => 'servicio',
            'nombre'           => 'nombre',
            'hectareas'        => 'hectáreas',
            'barrios'          => 'cantidad de barrios',
            'habitantes'       => 'cantidad de habitantes',
            'geojson'          => 'área en el mapa',
            'centro_lat'       => 'latitud del centro',
            'centro_lng'       => 'longitud del centro',
        ];
    }
}
