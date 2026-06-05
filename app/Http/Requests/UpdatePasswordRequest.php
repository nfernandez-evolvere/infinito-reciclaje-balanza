<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    protected $errorBag = 'updatePassword';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'         => 'Ingresá tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required'                 => 'La contraseña es obligatoria.',
            'password.confirmed'                => 'Las contraseñas no coinciden.',
            'password.min'                      => 'La contraseña debe tener al menos :min caracteres.',
            'password.letters'                  => 'La contraseña debe contener al menos una letra.',
            'password.mixed'                    => 'La contraseña debe contener mayúsculas y minúsculas.',
            'password.numbers'                  => 'La contraseña debe contener al menos un número.',
            'password.symbols'                  => 'La contraseña debe contener al menos un símbolo.',
            'password.uncompromised'            => 'Esta contraseña fue expuesta en una filtración de datos. Elegí una diferente.',
            'password_confirmation.required'    => 'Confirmá tu contraseña.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'contraseña actual',
            'password'         => 'nueva contraseña',
        ];
    }
}
