<?php

namespace App\Http\Requests;

use App\Models\Vehiculo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user?->isOperador() || $user?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'vehiculo_id'      => ['required', 'integer', 'exists:vehiculos,id'],
            'tipo_servicio_id' => ['required', 'integer', 'exists:tipos_servicio,id'],
            'zona_id'          => ['required', 'integer', 'exists:zonas,id'],
            // El turno es texto libre, copiado del que el operador eligió entre los
            // configurados para la zona (sin catálogo ni validación cruzada).
            'turno'         => ['nullable', 'string', 'max:20'],
            'peso_bruto_kg' => ['required', 'integer', 'min:1'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $vehiculoId = (int) $this->input('vehiculo_id');
            $pesoBruto = (int) $this->input('peso_bruto_kg');

            if (! $vehiculoId || ! $pesoBruto) {
                return;
            }

            $vehiculo = Vehiculo::find($vehiculoId);
            if (! $vehiculo) {
                return;
            }

            if ($pesoBruto < $vehiculo->tara_kg) {
                $v->errors()->add(
                    'peso_bruto_kg',
                    "El peso bruto ({$pesoBruto} kg) no puede ser menor a la tara del vehículo ({$vehiculo->tara_kg} kg)."
                );
            }
        });
    }
}
