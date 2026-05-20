<?php

namespace App\Http\Middleware;

use App\Models\Organizacion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganizacion
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        // Sin subdominio (acceso directo a dominio raíz) — no hay tenant
        if (count($parts) < 3) {
            app()->instance('organizacion', null);
            app()->instance('es_super_admin_context', false);
            return $next($request);
        }

        $raw    = $parts[0];
        $prefix = config('app.subdomain_prefix', '');
        $subdomain = ($prefix && str_starts_with($raw, $prefix))
            ? substr($raw, strlen($prefix))
            : $raw;

        // El subdominio 'super' es el contexto exclusivo del super_admin
        if ($subdomain === 'super') {
            app()->instance('organizacion', null);
            app()->instance('es_super_admin_context', true);
            return $next($request);
        }

        $org = Organizacion::where('slug', $subdomain)
            ->where('activo', true)
            ->first();

        if (! $org) {
            View::share('subdominio_invalido', $subdomain);
            abort(404);
        }

        app()->instance('organizacion', $org);
        app()->instance('es_super_admin_context', false);

        View::share('organizacion', $org);

        return $next($request);
    }
}
