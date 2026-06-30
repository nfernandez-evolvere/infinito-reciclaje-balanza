<?php

namespace Database\Seeders;

use App\Models\Organizacion;
use App\Models\ReporteConfiguracion;
use App\Models\TipoServicio;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use App\Models\ZonaTurno;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia datos previos en orden correcto (hijos antes que padres)
        DB::table('alertas')->delete();
        DB::table('config_alertas')->delete();
        DB::table('pesajes_log')->delete();
        DB::table('pesajes')->delete();
        DB::table('vehiculos_log')->delete();
        DB::table('zona_horarios')->delete();
        DB::table('zona_turnos')->delete();
        // zonas antes que tipos_servicio: zonas.tipo_servicio_id es noActionOnDelete.
        DB::table('zonas')->delete();
        DB::table('tipo_servicio_tipo_vehiculo')->delete();
        DB::table('vehiculos')->delete();
        DB::table('tipos_servicio')->delete();
        DB::table('tipos_vehiculo')->delete();
        DB::table('reportes_programados')->delete();
        DB::table('reporte_destinatarios')->delete();
        DB::table('reporte_configuraciones')->delete();
        DB::table('organizacion_user')->delete();
        DB::table('users')->delete();
        DB::table('organizaciones')->delete();

        $corrientes = Organizacion::create(['nombre' => 'Corrientes',  'activo' => true]);
        $resistencia = Organizacion::create(['nombre' => 'Resistencia', 'activo' => true]);

        User::create([
            'name'     => 'Nicolás Fernández',
            'email'    => 'nfernandez@evolvere.com.ar',
            'password' => 'Evolvere123!@',
            'role'     => 'super_admin',
        ]);

        $this->seedOrganizacion($corrientes, 'COR');
        $this->seedOrganizacion($resistencia, 'RES');

        // Admin con acceso a ambas orgs — para probar el selector de organización en el login
        $adminDoble = User::create([
            'name'     => 'Admin Doble',
            'email'    => 'admin.doble@test.com',
            'password' => 'Evolvere123!@',
            'role'     => 'admin',
        ]);
        $corrientes->users()->attach($adminDoble->id);
        $resistencia->users()->attach($adminDoble->id);

        $this->call(PesajeSeeder::class);
        $this->call(AlertaSeeder::class);
    }

    private function seedOrganizacion(Organizacion $org, string $suffix): void
    {
        // ── Usuarios ──────────────────────────────────────────────────────────
        $orgSlug = Str::slug($org->nombre);

        $admin = User::create([
            'name'     => "Admin $suffix",
            'email'    => "admin@{$orgSlug}.com",
            'password' => 'Evolvere123!@',
            'role'     => 'admin',
        ]);
        $org->users()->attach($admin->id);

        $operario = User::create([
            'name'     => "Operario $suffix",
            'email'    => "operario@{$orgSlug}.com",
            'password' => 'Evolvere123!@',
            'role'     => 'operador',
        ]);
        $org->users()->attach($operario->id);

        // ── Tipos de vehículo ───────────────────────────────────────────────
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
        // Áreas reales (aproximadas) de cada ciudad, con polígono GeoJSON para el mapa de calor.
        $zonasData = $this->zonasDeCiudad($org->nombre, $domiciliario->id, $barrido->id, $voluminoso->id);

        // ── Configuración de reportes ─────────────────────────────────────────
        ReporteConfiguracion::create([
            'organizacion_id'      => $org->id,
            'municipalidad_nombre' => "Municipalidad de {$org->nombre}",
            'intro_empresa'        => 'Infinito Reciclaje es una empresa dedicada a la gestión integral de residuos urbanos, brindando servicios de recolección, barrido y disposición final con estándares de calidad y cuidado ambiental.',
            'servicios'            => [
                ['titulo' => 'Recolección domiciliaria',  'descripcion' => 'Servicio de recolección puerta a puerta en zonas residenciales y comerciales.'],
                ['titulo' => 'Barrido de calles',         'descripcion' => 'Limpieza manual y mecánica de vías públicas y espacios comunes.'],
                ['titulo' => 'Recolección de voluminosos', 'descripcion' => 'Retiro de muebles, electrodomésticos y residuos de gran tamaño.'],
            ],
            'ai_enabled'                  => true,
            'ai_proveedor'                => 'gemini',
            'ai_modelo'                   => 'gemini-2.5-flash',
            'ai_prompt'                   => "Sos analista operativo de Infinito Reciclaje redactando la sección 'Oportunidades Estratégicas' del informe mensual para el municipio. Período: {periodo}.\n\nDatos del período:\n- Viajes realizados: {total_viajes}\n- Toneladas netas recolectadas: {toneladas} t\n- Días operativos: {dias_op} de {dias_rango}\n- Productividad promedio: {promedio_ton_dia} t/día\n- Top 3 zonas por volumen: {top3_zonas}\n- Zonas de mayor densidad (kg/ha): {densidad_zonas}\n\nReferencias para evaluación:\n- Productividad alta: > 80 t/día | Media: 40–80 t/día | Baja: < 40 t/día\n- Tasa de actividad óptima: > 85 % de los días del período\n- Zona crítica: concentra más del 35 % del volumen total\n\nRedactá exactamente 2 párrafos:\nPárrafo 1 — Diagnóstico: usá los datos y las referencias para evaluar si la productividad fue alta, media o baja; calculá la tasa de actividad e indicá si es óptima o deficitaria; nombrá la zona más crítica y si su concentración exige ajuste de frecuencia.\nPárrafo 2 — Oportunidades: planteá 2 acciones específicas y accionables para el próximo período, derivadas directamente de los datos (no genéricas). Cada acción debe nombrar la zona o métrica concreta que la justifica.\n\nEspañol formal. Sin encabezados, sin viñetas, sin saludos. Solo los dos párrafos.",
            'tipo_informe_mensual_activo' => true,
            'tipo_alertas_activo'         => false,
        ]);

        foreach ($zonasData as $data) {
            $geo = $this->geoFromCoords($data['coords']);

            // Modelo 1:N — la misma área se materializa como una zona por servicio.
            foreach ($data['servicios'] as $servicio) {
                $zona = Zona::create([
                    'organizacion_id'  => $org->id,
                    'tipo_servicio_id' => $servicio['id'],
                    'nombre'           => $data['nombre'],
                    'hectareas'        => $data['hectareas'],
                    'barrios'          => $data['barrios'],
                    'habitantes'       => $data['habitantes'],
                    'geojson'          => $geo['geojson'],
                    'centro_lat'       => $geo['centro_lat'],
                    'centro_lng'       => $geo['centro_lng'],
                ]);

                foreach ($servicio['turnos'] as $turno) {
                    ZonaTurno::create([
                        'zona_id' => $zona->id,
                        'turno'   => $turno,
                    ]);
                }
            }
        }
    }

    /**
     * Definición de zonas por ciudad: áreas reales (aproximadas) de Corrientes y Resistencia.
     * Los polígonos son bounding boxes ilustrativos alrededor de cada barrio — no son
     * límites municipales oficiales, pero ubican cada zona en su área real de la ciudad.
     *
     * @return array<int, array{nombre: string, hectareas: float, barrios: int, habitantes: int, coords: array<int, array{0: float, 1: float}>, servicios: array<int, array{id: int, turnos: array<int, string>}>}>
     */
    private function zonasDeCiudad(string $ciudad, int $domiciliario, int $barrido, int $voluminoso): array
    {
        return match ($ciudad) {
            'Corrientes' => [
                [
                    'nombre'    => 'Centro', 'hectareas' => 480.00, 'barrios' => 5, 'habitantes' => 38000,
                    'coords'    => [[-27.4585, -58.8410], [-27.4585, -58.8270], [-27.4730, -58.8270], [-27.4730, -58.8410]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna', 'Nocturna']],
                        ['id' => $barrido,      'turnos' => ['Diurna']],
                        ['id' => $voluminoso,   'turnos' => []],
                    ],
                ],
                [
                    'nombre'    => 'Costanera Norte', 'hectareas' => 720.00, 'barrios' => 7, 'habitantes' => 41000,
                    'coords'    => [[-27.4450, -58.8480], [-27.4450, -58.8300], [-27.4585, -58.8300], [-27.4585, -58.8480]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna', 'Nocturna']],
                        ['id' => $barrido,      'turnos' => ['Diurna']],
                    ],
                ],
                [
                    'nombre'    => 'Cambá Cuá', 'hectareas' => 1350.00, 'barrios' => 11, 'habitantes' => 52000,
                    'coords'    => [[-27.4800, -58.8300], [-27.4800, -58.8090], [-27.4980, -58.8090], [-27.4980, -58.8300]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna', 'Nocturna']],
                        ['id' => $barrido,      'turnos' => ['Diurna']],
                        ['id' => $voluminoso,   'turnos' => []],
                    ],
                ],
                [
                    'nombre'    => 'San Benito', 'hectareas' => 1640.00, 'barrios' => 9, 'habitantes' => 36000,
                    'coords'    => [[-27.4600, -58.8800], [-27.4600, -58.8560], [-27.4790, -58.8560], [-27.4790, -58.8800]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna']],
                        ['id' => $voluminoso,   'turnos' => []],
                    ],
                ],
                [
                    'nombre'    => 'Laguna Brava', 'hectareas' => 2900.00, 'barrios' => 4, 'habitantes' => 12000,
                    'coords'    => [[-27.4920, -58.8740], [-27.4920, -58.8500], [-27.5110, -58.8500], [-27.5110, -58.8740]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => []],
                        ['id' => $voluminoso,   'turnos' => []],
                    ],
                ],
            ],
            'Resistencia' => [
                [
                    'nombre'    => 'Centro', 'hectareas' => 520.00, 'barrios' => 6, 'habitantes' => 42000,
                    'coords'    => [[-27.4450, -58.9940], [-27.4450, -58.9780], [-27.4585, -58.9780], [-27.4585, -58.9940]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna', 'Nocturna']],
                        ['id' => $barrido,      'turnos' => ['Diurna']],
                    ],
                ],
                [
                    'nombre'    => 'Villa Don Andrés', 'hectareas' => 880.00, 'barrios' => 8, 'habitantes' => 34000,
                    'coords'    => [[-27.4220, -58.9940], [-27.4220, -58.9760], [-27.4385, -58.9760], [-27.4385, -58.9940]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna', 'Nocturna']],
                        ['id' => $barrido,      'turnos' => ['Diurna']],
                    ],
                ],
                [
                    'nombre'    => 'Barrio España', 'hectareas' => 1250.00, 'barrios' => 10, 'habitantes' => 47000,
                    'coords'    => [[-27.4640, -58.9920], [-27.4640, -58.9740], [-27.4805, -58.9740], [-27.4805, -58.9920]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna', 'Nocturna']],
                        ['id' => $barrido,      'turnos' => ['Diurna']],
                        ['id' => $voluminoso,   'turnos' => []],
                    ],
                ],
                [
                    'nombre'    => 'Villa Río Negro', 'hectareas' => 1580.00, 'barrios' => 7, 'habitantes' => 29000,
                    'coords'    => [[-27.4440, -59.0180], [-27.4440, -59.0000], [-27.4600, -59.0000], [-27.4600, -59.0180]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna']],
                        ['id' => $voluminoso,   'turnos' => []],
                    ],
                ],
                [
                    'nombre'    => 'Villa Prosperidad', 'hectareas' => 1120.00, 'barrios' => 6, 'habitantes' => 25000,
                    'coords'    => [[-27.4500, -58.9700], [-27.4500, -58.9520], [-27.4660, -58.9520], [-27.4660, -58.9700]],
                    'servicios' => [
                        ['id' => $domiciliario, 'turnos' => ['Diurna']],
                        ['id' => $barrido,      'turnos' => ['Diurna']],
                    ],
                ],
            ],
            default => [],
        };
    }

    /**
     * Construye el FeatureCollection GeoJSON y el centro a partir de un anillo de
     * coordenadas [lat, lng] en orden perimetral. GeoJSON usa el orden [lng, lat].
     *
     * @param  array<int, array{0: float, 1: float}>  $coords
     * @return array{geojson: string, centro_lat: float, centro_lng: float}
     */
    private function geoFromCoords(array $coords): array
    {
        $ring = array_map(fn ($p) => [$p[1], $p[0]], $coords);
        $ring[] = $ring[0]; // cerrar el anillo

        $fc = [
            'type'     => 'FeatureCollection',
            'features' => [[
                'type'       => 'Feature',
                'properties' => (object) [],
                'geometry'   => ['type' => 'Polygon', 'coordinates' => [$ring]],
            ]],
        ];

        $lats = array_column($coords, 0);
        $lngs = array_column($coords, 1);

        return [
            'geojson'    => json_encode($fc),
            'centro_lat' => round((min($lats) + max($lats)) / 2, 7),
            'centro_lng' => round((min($lngs) + max($lngs)) / 2, 7),
        ];
    }
}
