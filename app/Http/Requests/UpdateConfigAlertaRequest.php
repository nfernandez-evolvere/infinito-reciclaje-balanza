<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigAlertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización de rol la resuelve el middleware role:admin de la ruta.
        return true;
    }

    public function rules(): array
    {
        return [
            'config'                          => ['array'],
            'config.*.activo'                 => ['nullable'],
            'config.*.umbral_valor'           => ['nullable', 'numeric', 'min:0'],
            'config.gap_registro.hora_inicio' => ['nullable', 'date_format:H:i'],
            'config.gap_registro.hora_fin'    => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $inicio = $this->input('config.gap_registro.hora_inicio');
            $fin = $this->input('config.gap_registro.hora_fin');

            // Comparación lexicográfica válida por el formato 'HH:MM' con cero a la izquierda.
            if ($inicio && $fin && $fin <= $inicio) {
                $validator->errors()->add(
                    'config.gap_registro.hora_fin',
                    'La hora de fin debe ser posterior a la de inicio.',
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'config.gap_registro.hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
            'config.gap_registro.hora_fin.date_format'    => 'La hora de fin debe tener el formato HH:MM.',
        ];
    }
}
