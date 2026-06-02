<?php

namespace Database\Seeders;

use App\Models\Organizacion;
use App\Models\ReporteConfiguracion;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Models\ZonaServicio;
use App\Models\ZonaServicioTurno;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia datos previos en orden correcto (hijos antes que padres)
        DB::table('pesajes_log')->delete();
        DB::table('pesajes')->delete();
        DB::table('vehiculos_log')->delete();
        DB::table('zona_servicio_horarios')->delete();
        DB::table('zona_servicio_turnos')->delete();
        DB::table('zona_servicios')->delete();
        DB::table('tipo_servicio_tipo_vehiculo')->delete();
        DB::table('vehiculos')->delete();
        DB::table('tipos_servicio')->delete();
        DB::table('tipos_vehiculo')->delete();
        DB::table('zonas')->delete();
        DB::table('reportes_programados')->delete();
        DB::table('reporte_destinatarios')->delete();
        DB::table('reporte_configuraciones')->delete();
        DB::table('organizacion_user')->delete();
        DB::table('users')->delete();
        DB::table('organizaciones')->delete();

        $corrientes  = Organizacion::create(['nombre' => 'Corrientes',  'activo' => true]);
        $resistencia = Organizacion::create(['nombre' => 'Resistencia', 'activo' => true]);

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

        $this->call(PesajeSeeder::class);
    }

    private function seedOrganizacion(Organizacion $org, string $suffix): void
    {
        // ── Usuarios ──────────────────────────────────────────────────────────
        $orgSlug = Str::slug($org->nombre);

        $admin = User::create([
            'name'     => "Admin $suffix",
            'email'    => "admin@{$orgSlug}.com",
            'password' => '1234',
            'role'     => 'admin',
        ]);
        $org->users()->attach($admin->id);

        $operario = User::create([
            'name'     => "Operario $suffix",
            'email'    => "operario@{$orgSlug}.com",
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

        // ── Configuración de reportes ─────────────────────────────────────────
        ReporteConfiguracion::create([
            'organizacion_id'             => $org->id,
            'municipalidad_nombre'        => "Municipalidad de {$org->nombre}",
            'intro_empresa'               => 'Infinito Reciclaje es una empresa dedicada a la gestión integral de residuos urbanos, brindando servicios de recolección, barrido y disposición final con estándares de calidad y cuidado ambiental.',
            'servicios'                   => [
                ['titulo' => 'Recolección domiciliaria',  'descripcion' => 'Servicio de recolección puerta a puerta en zonas residenciales y comerciales.'],
                ['titulo' => 'Barrido de calles',         'descripcion' => 'Limpieza manual y mecánica de vías públicas y espacios comunes.'],
                ['titulo' => 'Recolección de voluminosos','descripcion' => 'Retiro de muebles, electrodomésticos y residuos de gran tamaño.'],
            ],
            'ai_enabled'                  => true,
            'ai_proveedor'                => 'gemini',
            'ai_modelo'                   => 'gemini-2.5-flash',
            'ai_prompt'                   => "Sos analista operativo de Infinito Reciclaje redactando la sección 'Oportunidades Estratégicas' del informe mensual para el municipio. Período: {periodo}.\n\nDatos del período:\n- Viajes realizados: {total_viajes}\n- Toneladas netas recolectadas: {toneladas} t\n- Días operativos: {dias_op} de {dias_rango}\n- Productividad promedio: {promedio_ton_dia} t/día\n- Top 3 zonas por volumen: {top3_zonas}\n- Zonas de mayor densidad (kg/ha): {densidad_zonas}\n\nReferencias para evaluación:\n- Productividad alta: > 80 t/día | Media: 40–80 t/día | Baja: < 40 t/día\n- Tasa de actividad óptima: > 85 % de los días del período\n- Zona crítica: concentra más del 35 % del volumen total\n\nRedactá exactamente 2 párrafos:\nPárrafo 1 — Diagnóstico: usá los datos y las referencias para evaluar si la productividad fue alta, media o baja; calculá la tasa de actividad e indicá si es óptima o deficitaria; nombrá la zona más crítica y si su concentración exige ajuste de frecuencia.\nPárrafo 2 — Oportunidades: planteá 2 acciones específicas y accionables para el próximo período, derivadas directamente de los datos (no genéricas). Cada acción debe nombrar la zona o métrica concreta que la justifica.\n\nEspañol formal. Sin encabezados, sin viñetas, sin saludos. Solo los dos párrafos.",
            'tipo_informe_mensual_activo' => true,
            'tipo_alertas_activo'         => false,
        ]);

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
