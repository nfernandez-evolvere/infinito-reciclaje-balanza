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
            'nombre'      => ['required', 'string', 'max:150'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_name'  => ['nullable', 'string', 'max:200'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre'      => 'nombre',
            'admin_email' => 'email del administrador',
            'admin_name'  => 'nombre del administrador',
        ];
    }
}
