<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organizacion;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total'     => Organizacion::count(),
            'activas'   => Organizacion::where('activo', true)->count(),
            'inactivas' => Organizacion::where('activo', false)->count(),
            'usuarios'  => User::where('role', '!=', 'super_admin')->count(),
        ];

        $recientes = Organizacion::withCount('users')
            ->latest()
            ->limit(5)
            ->get();

        return view('modules.super_admin.dashboard', compact('stats', 'recientes'));
    }
}
