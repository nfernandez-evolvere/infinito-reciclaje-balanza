<?php

namespace App\Services;

use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Support\Facades\Cache;

class ConfiguracionInicialService
{
    public static function forgetCache(): void
    {
        $orgId = app()->bound('organizacion') ? app('organizacion')?->id : 0;
        Cache::forget("config_inicial_v2_{$orgId}");
    }

    public function getProgress(): array
    {
        $orgId = app()->bound('organizacion') ? app('organizacion')?->id : 0;

        return Cache::remember("config_inicial_v2_{$orgId}", now()->addMinutes(10), function () {
            $steps = [
                [
                    'label'  => 'Tipos de vehículo',
                    'done'   => rescue(fn () => TipoVehiculo::where('activo', true)->exists(), false),
                    'route'  => 'admin.vehiculos.index',
                    'params' => ['tab' => 'tipos'],
                ],
                [
                    'label'  => 'Tipos de servicio',
                    'done'   => rescue(fn () => TipoServicio::where('activo', true)->exists(), false),
                    'route'  => 'admin.tipos-servicio.index',
                    'params' => [],
                ],
                [
                    'label'  => 'Zonas con servicios',
                    'done'   => rescue(fn () => Zona::where('activo', true)->whereHas('zonaServicios')->exists(), false),
                    'route'  => 'admin.zonas.index',
                    'params' => [],
                ],
                [
                    'label'  => 'Vehículos cargados',
                    'done'   => rescue(fn () => Vehiculo::where('activo', true)->exists(), false),
                    'route'  => 'admin.vehiculos.index',
                    'params' => [],
                ],
                [
                    'label'  => 'Operadores creados',
                    'done'   => rescue(fn () => User::where('role', 'operador')->where('activo', true)->exists(), false),
                    'route'  => 'admin.usuarios.index',
                    'params' => [],
                ],
            ];

            $completado = collect($steps)->where('done', true)->count();
            $total      = count($steps);

            return [
                'steps'      => $steps,
                'completado' => $completado,
                'total'      => $total,
                'porcentaje' => $total > 0 ? intval(($completado / $total) * 100) : 0,
            ];
        });
    }
}
