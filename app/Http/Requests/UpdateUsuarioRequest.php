<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $usuarioId = $this->route('usuario')?->id;

        return [
            'name'  => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuarioId)],
            'role'  => ['required', 'in:operador,admin'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'  => 'nombre',
            'email' => 'correo electrónico',
            'role'  => 'rol',
        ];
    }
}
