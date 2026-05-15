<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('modules.auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required'         => 'El correo electrónico es obligatorio.',
            'email.email'            => 'Ingresá un correo electrónico válido.',
            'password.required'      => 'La contraseña es obligatoria.',
            'password.confirmed'     => 'Las contraseñas no coinciden.',
            'password.min'           => 'La contraseña debe tener al menos :min caracteres.',
            'password_confirmation.required' => 'Confirmá tu contraseña.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        $messages = [
            Password::PASSWORD_RESET  => 'Tu contraseña fue restablecida. Ya podés ingresar.',
            Password::INVALID_TOKEN   => 'El enlace de recuperación no es válido o ya expiró.',
            Password::INVALID_USER    => 'No encontramos ninguna cuenta con ese correo electrónico.',
            Password::RESET_THROTTLED => 'Esperá unos minutos antes de intentar de nuevo.',
        ];

        $message = $messages[$status] ?? __($status);

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', $message)
            : back()->withInput($request->only('email'))->withErrors(['email' => $message]);
    }
}
