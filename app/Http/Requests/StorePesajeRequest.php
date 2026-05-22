<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isOperador() ?? false;
    }

    public function rules(): array
    {
        return [
            'vehiculo_id'      => ['required', 'integer', 'exists:vehiculos,id'],
            'tipo_servicio_id' => ['required', 'integer', 'exists:tipos_servicio,id'],
            'zona_id'          => ['required', 'integer', 'exists:zonas,id'],
            'turno'            => ['nullable', 'string', 'in:Diurna,Nocturna'],
            'peso_bruto_kg'    => ['required', 'integer', 'min:1'],
            'observaciones'    => ['nullable', 'string', 'max:500'],
        ];
    }
}
