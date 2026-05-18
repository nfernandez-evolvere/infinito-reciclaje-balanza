<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZonaServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_servicio_id'                => ['required', 'integer', 'exists:tipos_servicio,id'],
            'turnos'                          => ['nullable', 'array'],
            'turnos.*'                        => ['string', 'in:Diurna,Nocturna'],
            'horarios'                        => ['nullable', 'array'],
            'horarios.*'                      => ['nullable', 'array'],
            'horarios.*.*'                    => ['nullable', 'array'],
            'horarios.*.*.inicio'             => ['nullable', 'string', 'date_format:H:i'],
            'horarios.*.*.fin'                => ['nullable', 'string', 'date_format:H:i'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo_servicio_id' => 'tipo de servicio',
        ];
    }
}
