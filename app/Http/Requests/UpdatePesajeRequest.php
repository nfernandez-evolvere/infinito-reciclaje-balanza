<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
