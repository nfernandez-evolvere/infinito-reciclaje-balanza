<?php

namespace Database\Seeders;

use App\Models\Alerta;
use App\Models\ConfigAlerta;
use App\Models\Organizacion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlertaSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Organizacion::with('users')->get();

        foreach ($orgs as $org) {
            $adminIds = $org->users->where('role', 'admin')->pluck('id')->all();

            if (empty($adminIds)) {
                continue;
            }

            $this->seedConfigAlertas($org->id);
            $this->seedPesoFueraRango($org->id, $adminIds);
            $this->seedVolumenAtipico($org->id, $adminIds);
            $this->seedGapRegistro($org->id, $adminIds);
            $this->seedFrecuenciaZona($org->id, $adminIds);
            $this->seedVehiculoNoHabitual($org->id, $adminIds);
        }
    }

    private function seedConfigAlertas(int $orgId): void
    {
        foreach (ConfigAlerta::defaults() as $tipo => $defaults) {
            ConfigAlerta::updateOrCreate(
                ['organizacion_id' => $orgId, 'tipo' => $tipo],
                ['activo' => $defaults['activo'], 'umbral_valor' => $defaults['umbral_valor']],
            );
        }
    }

    private function seedPesoFueraRango(int $orgId, array $adminIds): void
    {
        $pesajes = DB::table('pesajes')
            ->where('organizacion_id', $orgId)
            ->where('alerta_peso', 1)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get(['id', 'zona_id', 'peso_bruto_kg', 'created_at']);

        foreach ($pesajes as $p) {
            $fecha = Carbon::parse($p->created_at)->toDateString();
            foreach ($adminIds as $adminId) {
                Alerta::create([
                    'organizacion_id' => $orgId,
                    'user_id'         => $adminId,
                    'tipo'            => 'peso_fuera_rango',
                    'titulo'          => 'Peso fuera de rango — pesaje #'.$p->id,
                    'descripcion'     => 'Peso bruto registrado: '.number_format($p->peso_bruto_kg).' kg, fuera del rango habitual para el tipo de vehículo.',
                    'pesaje_id'       => $p->id,
                    'zona_id'         => $p->zona_id,
                    'fecha_deteccion' => $fecha,
                    'leida'           => (bool) random_int(0, 1),
                ]);
            }
        }
    }

    private function seedVolumenAtipico(int $orgId, array $adminIds): void
    {
        $casos = [
            [-3, 'Volumen diario atípico', 'Se recolectaron 12.3 t (58% por debajo del promedio histórico de 29.5 t/día).'],
            [-8, 'Volumen diario atípico', 'Se recolectaron 51.2 t (73% por encima del promedio histórico de 29.5 t/día).'],
            [-15, 'Volumen diario atípico', 'Se recolectaron 8.7 t (70% por debajo del promedio histórico de 29.5 t/día).'],
        ];

        foreach ($casos as [$diasAtras, $titulo, $descripcion]) {
            $fecha = today()->subDays(abs($diasAtras))->toDateString();
            foreach ($adminIds as $adminId) {
                Alerta::create([
                    'organizacion_id' => $orgId,
                    'user_id'         => $adminId,
                    'tipo'            => 'volumen_diario_atipico',
                    'titulo'          => $titulo.' — '.today()->subDays(abs($diasAtras))->format('d/m/Y'),
                    'descripcion'     => $descripcion,
                    'fecha_deteccion' => $fecha,
                    'leida'           => $diasAtras < -7,
                ]);
            }
        }
    }

    private function seedGapRegistro(int $orgId, array $adminIds): void
    {
        $casos = [
            [-2, 'Sin actividad 145 min — ', 'No se registraron pesajes entre las 10:15 y las 12:40 (145 minutos).'],
            [-5, 'Sin actividad — ',          'No se registraron pesajes durante el horario operativo (08:00–18:00).'],
            [-12, 'Sin actividad 210 min — ', 'No se registraron pesajes entre las 08:00 y las 11:30 (210 minutos).'],
        ];

        foreach ($casos as [$diasAtras, $tituloPrefix, $descripcion]) {
            $fecha = today()->subDays(abs($diasAtras));
            foreach ($adminIds as $adminId) {
                Alerta::create([
                    'organizacion_id' => $orgId,
                    'user_id'         => $adminId,
                    'tipo'            => 'gap_registro',
                    'titulo'          => $tituloPrefix.$fecha->format('d/m/Y'),
                    'descripcion'     => $descripcion,
                    'fecha_deteccion' => $fecha->toDateString(),
                    'leida'           => $diasAtras < -7,
                ]);
            }
        }
    }

    private function seedFrecuenciaZona(int $orgId, array $adminIds): void
    {
        $zona = DB::table('zonas')->where('organizacion_id', $orgId)->first(['id', 'nombre']);
        if (! $zona) {
            return;
        }

        $casos = [
            [-4, '42% por encima del promedio de 18.3/día en los últimos 30 días.'],
            [-10, '35% por debajo del promedio de 18.3/día en los últimos 30 días.'],
        ];

        foreach ($casos as [$diasAtras, $descripcion]) {
            $fecha = today()->subDays(abs($diasAtras));
            foreach ($adminIds as $adminId) {
                Alerta::create([
                    'organizacion_id' => $orgId,
                    'user_id'         => $adminId,
                    'tipo'            => 'frecuencia_zona_atipica',
                    'titulo'          => 'Frecuencia atípica en zona — '.$fecha->format('d/m/Y'),
                    'descripcion'     => $zona->nombre.': '.$descripcion,
                    'zona_id'         => $zona->id,
                    'fecha_deteccion' => $fecha->toDateString(),
                    'leida'           => $diasAtras < -7,
                ]);
            }
        }
    }

    private function seedVehiculoNoHabitual(int $orgId, array $adminIds): void
    {
        $pesajes = DB::table('pesajes')
            ->join('vehiculos', 'pesajes.vehiculo_id', '=', 'vehiculos.id')
            ->join('tipos_servicio', 'pesajes.tipo_servicio_id', '=', 'tipos_servicio.id')
            ->where('pesajes.organizacion_id', $orgId)
            ->orderByDesc('pesajes.created_at')
            ->limit(5)
            ->get(['pesajes.id', 'pesajes.zona_id', 'vehiculos.patente', 'tipos_servicio.nombre as servicio']);

        foreach ($pesajes as $p) {
            foreach ($adminIds as $adminId) {
                Alerta::create([
                    'organizacion_id' => $orgId,
                    'user_id'         => $adminId,
                    'tipo'            => 'vehiculo_no_habitual',
                    'titulo'          => "Vehículo no habitual — {$p->patente}",
                    'descripcion'     => "Tipo de vehículo no coincide con los habituales para el servicio {$p->servicio}.",
                    'pesaje_id'       => $p->id,
                    'zona_id'         => $p->zona_id,
                    'fecha_deteccion' => today()->subDays(rand(1, 20))->toDateString(),
                    'leida'           => (bool) random_int(0, 1),
                ]);
            }
        }
    }
}
