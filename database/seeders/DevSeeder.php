<?php

namespace Database\Seeders;

use App\Models\Organizacion;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Models\ZonaServicio;
use App\Models\ZonaServicioTurno;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $corrientes  = Organizacion::create(['nombre' => 'Corrientes',  'slug' => 'corrientes',  'activo' => true]);
        $resistencia = Organizacion::create(['nombre' => 'Resistencia', 'slug' => 'resistencia', 'activo' => true]);

        User::create([
            'name'     => 'Nicolás Fernández',
            'email'    => 'nfernandez@evolvere.com.ar',
            'password' => '1234',
            'role'     => 'super_admin',
        ]);

        $this->seedOrganizacion($corrientes,  'COR');
        $this->seedOrganizacion($resistencia, 'RES');

        // Admin con acceso a ambas orgs — para probar el selector de organización en el login
        $adminDoble = User::create([
            'name'     => 'Admin Doble',
            'email'    => 'admin.doble@test.com',
            'password' => '1234',
            'role'     => 'admin',
        ]);
        $corrientes->users()->attach($adminDoble->id);
        $resistencia->users()->attach($adminDoble->id);
    }

    private function seedOrganizacion(Organizacion $org, string $suffix): void
    {
        // ── Usuarios ──────────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => "Admin $suffix",
            'email'    => "admin@{$org->slug}.com",
            'password' => '1234',
            'role'     => 'admin',
        ]);
        $org->users()->attach($admin->id);

        $operario = User::create([
            'name'     => "Operario $suffix",
            'email'    => "operario@{$org->slug}.com",
            'password' => '1234',
            'role'     => 'operador',
        ]);
        $org->users()->attach($operario->id);

        // ── Tipos de vehículo ─────────────────────────────────────────────────
        $compactador = TipoVehiculo::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Compactador $suffix",
            'peso_min_kg'     => 10000,
            'peso_max_kg'     => 26500,
        ]);
        $volcador = TipoVehiculo::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Volcador $suffix",
            'peso_min_kg'     => 13000,
            'peso_max_kg'     => 30000,
        ]);
        $volquete = TipoVehiculo::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Volquete $suffix",
            'peso_min_kg'     => 7000,
            'peso_max_kg'     => 20000,
        ]);
        $particular = TipoVehiculo::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Particular $suffix",
            'peso_min_kg'     => 1000,
            'peso_max_kg'     => 5000,
        ]);

        // ── Tipos de servicio ─────────────────────────────────────────────────
        $domiciliario = TipoServicio::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Domiciliario $suffix",
        ]);
        $domiciliario->tiposVehiculo()->sync([$compactador->id]);

        $voluminoso = TipoServicio::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Voluminoso $suffix",
        ]);
        $voluminoso->tiposVehiculo()->sync([$compactador->id]);

        $barrido = TipoServicio::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Barrido $suffix",
        ]);
        $barrido->tiposVehiculo()->sync([$volcador->id]);

        TipoServicio::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Servicios Especiales $suffix",
        ])->tiposVehiculo()->sync([$volcador->id]);

        TipoServicio::create([
            'organizacion_id' => $org->id,
            'nombre'          => "Centros de Transferencia $suffix",
        ])->tiposVehiculo()->sync([$volquete->id]);

        // ── Vehículos ─────────────────────────────────────────────────────────
        $titular = "Municipalidad de {$org->nombre}";

        foreach ([
            ["{$suffix}01", "{$suffix}C1", 8500,  $compactador->id, 10000, null],
            ["{$suffix}02", "{$suffix}C2", 9200,  $compactador->id, 10000, null],
            ["{$suffix}03", "{$suffix}V3", 11000, $volcador->id,    15000, null],
            ["{$suffix}04", "{$suffix}Q4", 6500,  $volquete->id,    null,  'Requiere acompañante para maniobras en espacios reducidos.'],
            ["{$suffix}05", "{$suffix}P5", 1800,  $particular->id,  null,  null],
        ] as [$patente, $interno, $tara, $tipoId, $cap, $obs]) {
            Vehiculo::create([
                'organizacion_id'  => $org->id,
                'patente'          => $patente,
                'numero_interno'   => $interno,
                'tara_kg'          => $tara,
                'tipo_vehiculo_id' => $tipoId,
                'titular'          => $titular,
                'capacidad_kg'     => $cap,
                'observaciones'    => $obs,
            ]);
        }

        // ── Zonas ─────────────────────────────────────────────────────────────
        $zonasData = [
            [
                'nombre' => "Zona Norte $suffix", 'hectareas' => 1850.50, 'barrios' => 12, 'habitantes' => 48000,
                'servicios' => [
                    ['id' => $domiciliario->id, 'turnos' => ['Diurna', 'Nocturna']],
                    ['id' => $barrido->id,      'turnos' => ['Diurna']],
                ],
            ],
            [
                'nombre' => "Zona Sur $suffix", 'hectareas' => 2200.00, 'barrios' => 15, 'habitantes' => 62000,
                'servicios' => [
                    ['id' => $domiciliario->id, 'turnos' => ['Diurna', 'Nocturna']],
                    ['id' => $barrido->id,      'turnos' => ['Diurna']],
                    ['id' => $voluminoso->id,   'turnos' => []],
                ],
            ],
            [
                'nombre' => "Zona Centro $suffix", 'hectareas' => 620.00, 'barrios' => 6, 'habitantes' => 35000,
                'servicios' => [
                    ['id' => $domiciliario->id, 'turnos' => ['Diurna']],
                    ['id' => $barrido->id,      'turnos' => ['Diurna']],
                    ['id' => $voluminoso->id,   'turnos' => []],
                ],
            ],
            [
                'nombre' => "Zona Industrial $suffix", 'hectareas' => 3400.00, 'barrios' => 3, 'habitantes' => 8000,
                'servicios' => [
                    ['id' => $domiciliario->id, 'turnos' => []],
                ],
            ],
        ];

        foreach ($zonasData as $data) {
            $zona = Zona::create([
                'organizacion_id' => $org->id,
                'nombre'          => $data['nombre'],
                'hectareas'       => $data['hectareas'],
                'barrios'         => $data['barrios'],
                'habitantes'      => $data['habitantes'],
            ]);

            foreach ($data['servicios'] as $servicio) {
                ZonaServicio::create([
                    'zona_id'          => $zona->id,
                    'tipo_servicio_id' => $servicio['id'],
                ]);

                foreach ($servicio['turnos'] as $turno) {
                    ZonaServicioTurno::create([
                        'zona_id'          => $zona->id,
                        'tipo_servicio_id' => $servicio['id'],
                        'turno'            => $turno,
                    ]);
                }
            }
        }
    }
}
