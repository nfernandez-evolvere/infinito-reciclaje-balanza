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
        $vehiculo = $this->route('vehiculo');
        $vehiculoId = $vehiculo?->id;

        // Cuando la tara cambia y el vehículo ya tiene pesajes, el admin debe
        // declarar si corrige un dato mal cargado (recalcula el historial) o
        // registra un cambio real del vehículo (solo afecta a futuro).
        $taraCambio = $vehiculo && (int) $this->input('tara_kg') !== (int) $vehiculo->tara_kg;
        $tienePesajes = $vehiculo ? $vehiculo->pesajes()->exists() : false;
        $requiereDecision = $taraCambio && $tienePesajes;

        return [
            'patente'          => ['required', 'string', 'max:20', Rule::unique('vehiculos', 'patente')->ignore($vehiculoId)],
            'numero_interno'   => ['required', 'string', 'max:20', Rule::unique('vehiculos', 'numero_interno')->ignore($vehiculoId)],
            'tara_kg'          => ['required', 'integer', 'min:1'],
            'tipo_vehiculo_id' => ['required', 'integer', 'exists:tipos_vehiculo,id'],
            'titular'          => ['required', 'string', 'max:200'],
            'capacidad_kg'     => ['nullable', 'integer', 'min:1'],
            'observaciones'    => ['nullable', 'string', 'max:500'],
            '_intencion_tara'  => [Rule::requiredIf($requiereDecision), 'nullable', 'in:corregir_dato,cambio_real'],
            '_motivo_tara'     => [Rule::requiredIf($requiereDecision), 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            '_intencion_tara.required' => 'Elegí si es una corrección de un dato mal cargado o un cambio real de la tara.',
            '_intencion_tara.in'       => 'La opción seleccionada no es válida.',
            '_motivo_tara.required'    => 'Describí el motivo del cambio de tara.',
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
            '_intencion_tara'  => 'tipo de corrección',
            '_motivo_tara'     => 'motivo',
        ];
    }
}
