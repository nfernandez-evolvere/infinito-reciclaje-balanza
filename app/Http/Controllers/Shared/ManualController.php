<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ManualController extends Controller
{
    private const ADMIN_DOCS = [
        'configuracion-inicial' => ['label' => 'Configuración inicial', 'icon' => 'list-checks'],
        'onboarding-admin'      => ['label' => 'Guía de inicio',        'icon' => 'book-open'],
        'modulo-dashboard'      => ['label' => 'Dashboard',             'icon' => 'layout-dashboard'],
        'modulo-pesajes-admin'  => ['label' => 'Pesajes',               'icon' => 'scale'],
        'modulo-reportes'       => ['label' => 'Reportes',              'icon' => 'file-bar-chart'],
        'modulo-alarmas'        => ['label' => 'Alertas',               'icon' => 'triangle-alert'],
        'modulo-abms'           => ['label' => 'Padrones',              'icon' => 'database'],
    ];

    private const OPERADOR_DOCS = [
        'onboarding-operador' => ['label' => 'Guía de inicio',    'icon' => 'book-open'],
        'modulo-balanza'      => ['label' => 'Registro de pesaje', 'icon' => 'scale'],
    ];

    public function show(?string $slug = null): View
    {
        /** @var User $user */
        $user = Auth::user();
        $docs = $user->isAdmin() ? self::ADMIN_DOCS : self::OPERADOR_DOCS;

        $slug ??= array_key_first($docs);

        abort_unless(array_key_exists($slug, $docs), 404);

        $path = base_path("docs/knowledge/{$slug}.md");
        abort_unless(file_exists($path), 404);

        $raw = file_get_contents($path);

        // Quitar el h1 inicial — se muestra en el header de la card
        $raw = preg_replace('/^# [^\n]+\n?/', '', $raw, 1);

        $content = Str::markdown(
            $raw,
            ['html_input' => 'strip', 'allow_unsafe_links' => false]
        );

        return view('modules.shared.manual', compact('docs', 'slug', 'content'));
    }
}
