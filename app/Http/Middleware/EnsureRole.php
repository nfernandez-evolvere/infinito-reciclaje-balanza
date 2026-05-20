<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // super_admin tiene acceso a cualquier ruta protegida por rol
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if ($user->role !== $role) {
            abort(403);
        }

        return $next($request);
    }
}
