<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        $usuariosPrueba = null;

        if (app()->environment('local', 'staging')) {
            $esSuperAdmin = app()->bound('es_super_admin_context') && app('es_super_admin_context');
            $org = app()->bound('organizacion') ? app('organizacion') : null;

            if ($esSuperAdmin) {
                $usuariosPrueba = User::withoutGlobalScopes()
                    ->where('role', 'super_admin')
                    ->select('name', 'email', 'role')
                    ->get();
            } elseif ($org) {
                $usuariosPrueba = User::where('organizacion_id', $org->id)
                    ->whereIn('role', ['admin', 'operador'])
                    ->select('name', 'email', 'role')
                    ->orderBy('role')
                    ->get();
            }
        }

        return view('modules.auth.login', compact('usuariosPrueba'));
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        return redirect()->intended(match(true) {
            $user->isSuperAdmin() => route('super.dashboard'),
            $user->isAdmin()      => route('admin.dashboard'),
            default               => route('balanza'),
        });
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
