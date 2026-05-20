<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug'   => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:organizaciones,slug'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'slug'   => 'slug',
        ];
    }
}
