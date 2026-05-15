<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('modules.auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'Ingresá un correo electrónico válido.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        $messages = [
            Password::RESET_LINK_SENT => 'Te enviamos un enlace para restablecer tu contraseña. Revisá tu correo.',
            Password::INVALID_USER    => 'No encontramos ninguna cuenta con ese correo electrónico.',
            Password::RESET_THROTTLED => 'Esperá unos minutos antes de solicitar otro enlace.',
        ];

        $message = $messages[$status] ?? __($status);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', $message)
            : back()->withInput($request->only('email'))->withErrors(['email' => $message]);
    }
}
