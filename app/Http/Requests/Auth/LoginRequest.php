<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'           => ['required', 'string', 'email'],
            'password'        => ['required', 'string'],
            'organizacion_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'Ingresá un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if (! $user->activo) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está desactivada. Contactá al administrador.',
            ]);
        }

        $orgId = $this->input('organizacion_id');
        $esSuperContexto = is_null($orgId) || $orgId === '' || $orgId === 'super';

        if ($user->isSuperAdmin()) {
            // Super admin solo puede ingresar desde "Administración del sistema"
            if (! $esSuperContexto) {
                Auth::logout();
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
        } else {
            // Usuarios de org: deben seleccionar una org y pertenecer a ella
            if ($esSuperContexto) {
                Auth::logout();
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            $org = $user->organizaciones()
                ->where('organizaciones.id', $orgId)
                ->where('activo', true)
                ->first();

            if (! $org) {
                Auth::logout();
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            session(['organizacion_id' => $org->id]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
