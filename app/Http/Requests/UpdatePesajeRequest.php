<?php

namespace App\Http\Requests;

use App\Models\Pesaje;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user?->isOperador() || $user?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'peso_bruto_kg'    => ['sometimes', 'integer', 'min:1'],
            'tipo_servicio_id' => ['sometimes', 'integer', 'exists:tipos_servicio,id'],
            'zona_id'          => ['sometimes', 'integer', 'exists:zonas,id'],
            // Texto libre, copiado del turno elegido para la zona (sin catálogo).
            'turno'         => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'motivo'        => ['required', 'string', 'min:1', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $pesoBruto = (int) $this->input('peso_bruto_kg');

            // Sin peso válido no corren las reglas cruzadas: las reglas base
            // (integer, min:1) ya cubren ese caso.
            if (! $this->filled('peso_bruto_kg') || $pesoBruto <= 0) {
                return;
            }

            $pesaje = $this->route('pesaje');
            $vehiculo = $pesaje instanceof Pesaje
                ? $pesaje->vehiculo?->loadMissing('tipoVehiculo')
                : null;
            if (! $vehiculo) {
                return;
            }

            if ($pesoBruto < $vehiculo->tara_kg) {
                $v->errors()->add(
                    'peso_bruto_kg',
                    "El peso bruto ({$pesoBruto} kg) no puede ser menor a la tara del vehículo ({$vehiculo->tara_kg} kg)."
                );
            }

            $tope = $vehiculo->tipoVehiculo?->peso_tope_kg;
            if ($tope !== null && $pesoBruto > $tope) {
                $v->errors()->add(
                    'peso_bruto_kg',
                    "El peso bruto ({$pesoBruto} kg) supera el máximo permitido para {$vehiculo->tipoVehiculo->nombre} ({$tope} kg). Revisá el valor ingresado."
                );
            }
        });
    }
}
