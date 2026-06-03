<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EgresoPesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user?->isOperador() || $user?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'bruto_salida_kg' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
