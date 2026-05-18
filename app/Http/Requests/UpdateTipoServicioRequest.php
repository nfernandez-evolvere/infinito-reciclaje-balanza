<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $tipoId = $this->route('tipos_servicio')?->id;

        return [
            'nombre'                    => [
                'required',
                'string',
                'max:100',
                Rule::unique('tipos_servicio', 'nombre')->ignore($tipoId),
            ],
            'tipo_vehiculo_sugerido_id' => ['nullable', 'integer', 'exists:tipos_vehiculo,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'                   => 'nombre',
            'tipo_vehiculo_sugerido_id' => 'vehículo habitual',
        ];
    }
}
