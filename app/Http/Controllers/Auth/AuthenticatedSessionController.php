<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
            $usuariosPrueba = User::withoutGlobalScopes()
                ->orderBy('role')
                ->orderBy('name')
                ->select('name', 'email', 'role')
                ->get();
        }

        return view('modules.auth.login', compact('usuariosPrueba'));
    }

    public function fetchOrganizaciones(Request $request): JsonResponse
    {
        $email = $request->query('email', '');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['orgs' => []]);
        }

        $user = User::withoutGlobalScopes()
            ->where('email', $email)
            ->where('activo', true)
            ->first();

        if (! $user) {
            return response()->json(['orgs' => []]);
        }

        if ($user->isSuperAdmin()) {
            return response()->json(['super_admin' => true]);
        }

        $orgs = $user->organizaciones()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['organizaciones.id', 'organizaciones.nombre']);

        return response()->json([
            'orgs' => $orgs->map(fn ($o) => ['id' => $o->id, 'nombre' => $o->nombre])->values(),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        return redirect()->intended(match (true) {
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
