<?php

namespace Database\Seeders;

use App\Models\Organizacion;
use App\Models\Zona;
use App\Models\ZonaServicioTurno;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Genera pesajes ficticios realistas para todas las organizaciones activas.
 *
 * Período: 1 de enero 2026 → hoy
 * Volumen: ~35–55 pesajes/día operativo (Lun–Sáb) por organización
 * Resultado esperado: ~8.000–12.000 pesajes en total para 2 organizaciones
 *
 * Distribución de flota: ponderada — compactadores y volcadores con mayor frecuencia.
 * Distribución de pesos: aproximación a curva normal por tipo de vehículo.
 * Datos adicionales: ~3% con alerta_peso, ~2% editados con pesajes_log.
 */
class PesajeSeeder extends Seeder
{
    private const DESDE = [2026, 1, 1];
    private const BATCH_SIZE = 500;

    // Mensajes de motivo para pesajes editados
    private const MOTIVOS = [
        'Corrección por error de lectura en balanza.',
        'Ajuste post-verificación manual del peso.',
        'Error tipográfico al ingresar el valor.',
        'Corregido según planilla de control del operador.',
        'El display de la balanza mostró valor incorrecto al momento del registro.',
    ];

    // Observaciones opcionales (~5% de los pesajes)
    private const OBSERVACIONES = [
        'Carga con exceso de humedad.',
        'Material compactado en dos pasadas.',
        'Vehículo llegó con carga parcial por ruta corta.',
        'Contiene residuos voluminosos de barrido especial.',
        'Operación en horario extendido por feriado.',
    ];

    public function run(): void
    {
        $orgs = Organizacion::activas()->with([
            'vehiculos.tipoVehiculo',
            'users' => fn ($q) => $q->where('role', 'operador'),
        ])->get();

        foreach ($orgs as $org) {
            $this->command->info("  → {$org->nombre}: construyendo combinaciones...");
            $combinaciones = $this->buildCombinaciones($org);

            if ($combinaciones->isEmpty()) {
                $this->command->warn("     Sin combinaciones zona+servicio. Saltando.");
                continue;
            }

            $vehiculos  = $org->vehiculos;
            $operadores = $org->users;

            if ($vehiculos->isEmpty() || $operadores->isEmpty()) {
                $this->command->warn("     Sin vehículos u operadores. Saltando.");
                continue;
            }

            $vehiculosPonderados = $this->buildFlotaPonderada($vehiculos);

            $this->command->info("  → {$org->nombre}: generando pesajes...");
            $total = $this->generarPesajes($org, $combinaciones, $vehiculosPonderados, $operadores);

            $this->command->info("  → {$org->nombre}: agregando ediciones...");
            $this->agregarEdiciones($org);

            $this->command->info("  ✓ {$org->nombre}: {$total} pesajes generados.");
        }
    }

    // ─── Generación principal ─────────────────────────────────────────────────

    private function generarPesajes(
        Organizacion $org,
        Collection $combinaciones,
        Collection $flotaPonderada,
        Collection $operadores,
    ): int {
        $desde = Carbon::create(...self::DESDE);
        $hasta = Carbon::today();
        $batch = [];
        $total = 0;

        foreach (CarbonPeriod::create($desde, $hasta) as $dia) {
            /** @var Carbon $dia */
            if ($dia->isSunday()) continue;

            $volumen = $this->volumenDia($dia, $org->nombre);

            for ($i = 0; $i < $volumen; $i++) {
                $hora = $this->horaAleatoria($dia);

                // Hoy: no generar pesajes futuros
                if ($dia->isToday() && $hora->gt(now())) continue;

                $combinacion = $combinaciones->random();
                $vehiculo    = $flotaPonderada->random();
                $operador    = $operadores->random();

                $neto  = $this->generarNeto($vehiculo->tipoVehiculo->nombre);
                $bruto = $vehiculo->tara_kg + $neto;

                $alerta = $bruto < $vehiculo->tipoVehiculo->peso_min_kg
                       || $bruto > $vehiculo->tipoVehiculo->peso_max_kg;

                // Hoy: los pesajes de las últimas 2 horas pueden estar En predio
                $enPredio = $dia->isToday()
                    && $hora->diffInMinutes(now()) < 120
                    && rand(1, 100) <= 35;

                $batch[] = [
                    'uuid'             => Str::uuid()->toString(),
                    'organizacion_id'  => $org->id,
                    'vehiculo_id'      => $vehiculo->id,
                    'operador_id'      => $operador->id,
                    'tipo_servicio_id' => $combinacion['tipo_servicio_id'],
                    'zona_id'          => $combinacion['zona_id'],
                    'turno'            => $combinacion['turno'],
                    'peso_bruto_kg'    => $bruto,
                    'peso_tara_kg'     => $vehiculo->tara_kg,
                    'peso_neto_kg'     => $neto,
                    'alerta_peso'      => $alerta ? 1 : 0,
                    'observaciones'    => rand(1, 20) === 1
                        ? self::OBSERVACIONES[array_rand(self::OBSERVACIONES)]
                        : null,
                    'estado'           => $enPredio ? 'En predio' : 'Cerrado',
                    'hora_salida'      => $enPredio
                        ? null
                        : $hora->copy()->addMinutes(rand(15, 90))->format('Y-m-d H:i:s'),
                    'bruto_salida_kg'  => null,
                    'editado'          => 0,
                    'created_at'       => $hora->format('Y-m-d H:i:s'),
                    'updated_at'       => $hora->format('Y-m-d H:i:s'),
                ];

                $total++;

                if (count($batch) >= self::BATCH_SIZE) {
                    DB::table('pesajes')->insert($batch);
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            DB::table('pesajes')->insert($batch);
        }

        return $total;
    }

    // ─── Ediciones auditadas ──────────────────────────────────────────────────

    private function agregarEdiciones(Organizacion $org): void
    {
        $admins = $org->users()->where('role', 'admin')->get();
        if ($admins->isEmpty()) return;

        // Tomar ~2% del total como editados (máx. 80 por org)
        $pesajes = DB::table('pesajes')
            ->where('organizacion_id', $org->id)
            ->where('estado', 'Cerrado')
            ->whereDate('created_at', '<', today())
            ->inRandomOrder()
            ->limit(min(80, (int) (DB::table('pesajes')->where('organizacion_id', $org->id)->count() * 0.02)))
            ->get(['id', 'peso_bruto_kg', 'peso_tara_kg', 'created_at']);

        if ($pesajes->isEmpty()) return;

        $logBatch = [];

        foreach ($pesajes as $p) {
            $originalBruto = $p->peso_bruto_kg;
            $delta         = rand(-600, 600);
            $nuevoBruto    = max(1000, $originalBruto + $delta);
            $nuevoNeto     = $nuevoBruto - $p->peso_tara_kg;

            DB::table('pesajes')->where('id', $p->id)->update([
                'peso_bruto_kg' => $nuevoBruto,
                'peso_neto_kg'  => $nuevoNeto,
                'editado'       => 1,
                'updated_at'    => Carbon::parse($p->created_at)->addHours(rand(1, 48))->format('Y-m-d H:i:s'),
            ]);

            $logBatch[] = [
                'pesaje_id'      => $p->id,
                'campo'          => 'peso_bruto_kg',
                'valor_anterior' => (string) $originalBruto,
                'valor_nuevo'    => (string) $nuevoBruto,
                'motivo'         => self::MOTIVOS[array_rand(self::MOTIVOS)],
                'usuario_id'     => $admins->random()->id,
                'created_at'     => Carbon::parse($p->created_at)->addHours(rand(1, 48))->format('Y-m-d H:i:s'),
            ];
        }

        if (!empty($logBatch)) {
            DB::table('pesajes_log')->insert($logBatch);
        }
    }

    // ─── Helpers de construcción ──────────────────────────────────────────────

    /**
     * Construye todas las combinaciones válidas (zona_id, tipo_servicio_id, turno)
     * para una organización. Cada entrada es un array con esas tres claves.
     */
    private function buildCombinaciones(Organizacion $org): Collection
    {
        $combinaciones = collect();

        $zonas = Zona::where('organizacion_id', $org->id)
            ->with('zonaServicios')
            ->get();

        foreach ($zonas as $zona) {
            foreach ($zona->zonaServicios as $zs) {
                $turnos = ZonaServicioTurno::where('zona_id', $zona->id)
                    ->where('tipo_servicio_id', $zs->tipo_servicio_id)
                    ->pluck('turno');

                if ($turnos->isEmpty()) {
                    $combinaciones->push([
                        'zona_id'          => $zona->id,
                        'tipo_servicio_id' => $zs->tipo_servicio_id,
                        'turno'            => null,
                    ]);
                } else {
                    foreach ($turnos as $turno) {
                        $combinaciones->push([
                            'zona_id'          => $zona->id,
                            'tipo_servicio_id' => $zs->tipo_servicio_id,
                            'turno'            => $turno,
                        ]);
                    }
                }
            }
        }

        return $combinaciones;
    }

    /**
     * Duplica los vehículos en la colección según su peso relativo.
     * Compactadores y volcadores aparecen 4× más que particulares.
     */
    private function buildFlotaPonderada(Collection $vehiculos): Collection
    {
        $ponderada = collect();

        foreach ($vehiculos as $v) {
            $nombre = $v->tipoVehiculo->nombre ?? '';
            $peso   = match (true) {
                str_contains($nombre, 'Compactador') => 4,
                str_contains($nombre, 'Volcador')    => 4,
                str_contains($nombre, 'Volquete')    => 2,
                default                              => 1,
            };

            for ($i = 0; $i < $peso; $i++) {
                $ponderada->push($v);
            }
        }

        return $ponderada;
    }

    // ─── Helpers de generación ────────────────────────────────────────────────

    /**
     * Cantidad de pesajes para un día. Varía según:
     * - Día de la semana (sábados menos)
     * - Mes (enero bajo, marzo pico)
     * - Organización (corrientes tiene mayor volumen)
     * - Varianza aleatoria diaria ±20%
     */
    private function volumenDia(Carbon $dia, string $orgNombre): int
    {
        $base = $orgNombre === 'Corrientes' ? 48 : 32;

        $factorDia = match (true) {
            $dia->isSaturday() => 0.50,
            $dia->isFriday()   => 0.88,
            $dia->isMonday()   => 0.92,
            default            => 1.00,
        };

        $factorMes = match ($dia->month) {
            1  => 0.72,
            2  => 0.82,
            3  => 1.05,
            4  => 1.00,
            5  => 0.93,
            default => 1.00,
        };

        // Hoy: proporcional al % del día transcurrido en horario operativo (6–18 h)
        $factorHoy = 1.0;
        if ($dia->isToday()) {
            $minutosOp    = 12 * 60; // ventana operativa
            $minutosTransc = max(0, now()->diffInMinutes(Carbon::today()->setHour(6)));
            $factorHoy     = min(1.0, $minutosTransc / $minutosOp);
        }

        $varianza = rand(80, 120) / 100.0;

        return max(1, (int) round($base * $factorDia * $factorMes * $factorHoy * $varianza));
    }

    /**
     * Peso neto en kg basado en el tipo de vehículo.
     * Aproxima distribución normal con media y desvío estándar por tipo.
     * Usa promedio de 3 uniformes (Teorema Central del Límite simple).
     */
    private function generarNeto(string $nombreTipo): int
    {
        [$media, $desvio] = match (true) {
            str_contains($nombreTipo, 'Compactador') => [5600, 1400],
            str_contains($nombreTipo, 'Volcador')    => [4400, 1200],
            str_contains($nombreTipo, 'Volquete')    => [2400,  800],
            default                                  => [2000,  600],
        };

        // Suma de 3 uniformes → distribución triangular, buen proxy de normal
        $u = (rand(0, 10000) + rand(0, 10000) + rand(0, 10000)) / 30000.0;
        // Escalar: [0,1] → [-1, 1] con stddev ≈ 1/√3 ≈ 0.577
        $z    = ($u - 0.5) * 2;
        $neto = (int) round($media + $z * $desvio * 1.732);

        return max(200, $neto);
    }

    /**
     * Hora aleatoria para el día. Concentra el 70% en el pico 7–14 h,
     * el resto distribuido entre 6–7 h y 14–18 h.
     */
    private function horaAleatoria(Carbon $dia): Carbon
    {
        if (rand(1, 10) <= 7) {
            // Pico
            $hora   = rand(7, 13);
            $minuto = rand(0, 59);
        } elseif (rand(0, 1) === 0) {
            // Madrugada operativa
            $hora   = rand(6, 6);
            $minuto = rand(0, 59);
        } else {
            // Tarde
            $hora   = rand(14, 17);
            $minuto = rand(0, 59);
        }

        return $dia->copy()
            ->setHour($hora)
            ->setMinute($minuto)
            ->setSecond(rand(0, 59));
    }
}
