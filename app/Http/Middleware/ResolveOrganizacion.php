<?php

namespace App\Http\Middleware;

use App\Models\Organizacion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganizacion
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->isSuperAdmin()) {
            $orgId = session('organizacion_id');

            if ($orgId) {
                $org = Auth::user()
                    ->organizaciones()
                    ->where('organizaciones.id', $orgId)
                    ->where('activo', true)
                    ->first();

                if ($org) {
                    app()->instance('organizacion', $org);
                    View::share('organizacion', $org);
                }
            }
        }

        return $next($request);
    }
}
