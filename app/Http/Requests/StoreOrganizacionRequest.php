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
            'nombre'                  => ['required', 'string', 'max:150'],
            'slug'                    => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:organizaciones,slug'],
            'admin_email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password'          => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'         => 'nombre',
            'slug'           => 'slug',
            'admin_email'    => 'email del administrador',
            'admin_password' => 'contraseña del administrador',
        ];
    }
}
