<?php

namespace App\Services;

use App\Models\Organizacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SuperAdminDashboardService
{
    public function stats(): array
    {
        return [
            'total'     => Organizacion::count(),
            'activas'   => Organizacion::where('activo', true)->count(),
            'inactivas' => Organizacion::where('activo', false)->count(),
            'usuarios'  => User::where('role', '!=', 'super_admin')->count(),
        ];
    }

    public function recientes(int $limit = 5): Collection
    {
        return Organizacion::withCount('users')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
