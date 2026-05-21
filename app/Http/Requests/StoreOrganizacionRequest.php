<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $emailExists = User::where('email', $this->admin_email)->exists();

        return [
            'nombre'                  => ['required', 'string', 'max:150'],
            'slug'                    => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:organizaciones,slug'],
            'admin_email'             => ['required', 'email', 'max:255'],
            'admin_password'          => [Rule::when(! $emailExists, ['required', 'string', 'min:8', 'confirmed'], ['nullable'])],
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
