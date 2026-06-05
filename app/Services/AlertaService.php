<?php

namespace App\Services;

use App\Models\ConfigAlerta;
use App\Models\Pesaje;
use App\Models\User;
use App\Repositories\AlertaRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AlertaService
{
    public function __construct(
        protected AlertaRepository $alertaRepository,
    ) {}

    // ── Registro manual (llamado desde PesajeService) ─────────────────

    public function registrarPesoFueraRango(Pesaje $pesaje): void
    {
        $config = $this->alertaRepository->getConfig($pesaje->organizacion_id, 'peso_fuera_rango');
        if ($config && ! $config->activo) {
            return;
        }

        if ($this->alertaRepository->existeHoy($pesaje->organizacion_id, 'peso_fuera_rango', now(), $pesaje->id)) {
            return;
        }

        $tipo = $pesaje->vehiculo?->tipoVehiculo;
        $rango = $tipo
            ? number_format($tipo->peso_min_kg).' – '.number_format($tipo->peso_max_kg).' kg'
            : 'rango no definido';

        $base = [
            'organizacion_id' => $pesaje->organizacion_id,
            'tipo'            => 'peso_fuera_rango',
            'titulo'          => "Peso fuera de rango — {$pesaje->vehiculo?->patente}",
            'descripcion'     => 'Peso bruto: '.number_format($pesaje->peso_bruto_kg)." kg. Rango habitual para {$tipo?->nombre}: {$rango}.",
            'pesaje_id'       => $pesaje->id,
            'zona_id'         => $pesaje->zona_id,
            'fecha_deteccion' => today()->toDateString(),
        ];

        foreach ($this->getAdminIds($pesaje->organizacion_id) as $adminId) {
            $this->alertaRepository->create(array_merge($base, ['user_id' => $adminId]));
        }
    }

    private function getAdminIds(int $organizacionId): array
    {
        return User::whereHas('organizaciones', fn ($q) => $q->where('organizaciones.id', $organizacionId))
            ->where('role', 'admin')
            ->pluck('id')
            ->all();
    }

    private function createParaAdmins(int $organizacionId, array $data): void
    {
        foreach ($this->getAdminIds($organizacionId) as $adminId) {
            $this->alertaRepository->create(array_merge($data, [
                'organizacion_id' => $organizacionId,
                'user_id'         => $adminId,
            ]));
        }
    }

    public function registrarVehiculoNoHabitual(Pesaje $pesaje): void
    {
        $config = $this->alertaRepository->getConfig($pesaje->organizacion_id, 'vehiculo_no_habitual');
        if ($config && ! $config->activo) {
            return;
        }

        if ($this->alertaRepository->existeHoy($pesaje->organizacion_id, 'vehiculo_no_habitual', now(), $pesaje->id)) {
            return;
        }

        $servicio = $pesaje->tipoServicio;
        $tipoVeh = $pesaje->vehiculo?->tipoVehiculo;
        $habituales = $servicio?->tiposVehiculo->pluck('nombre')->join(', ') ?? '—';

        $base = [
            'organizacion_id' => $pesaje->organizacion_id,
            'tipo'            => 'vehiculo_no_habitual',
            'titulo'          => "Vehículo no habitual — {$pesaje->vehiculo?->patente}",
            'descripcion'     => "Tipo: {$tipoVeh?->nombre}. Habituales para {$servicio?->nombre}: {$habituales}.",
            'pesaje_id'       => $pesaje->id,
            'zona_id'         => $pesaje->zona_id,
            'fecha_deteccion' => today()->toDateString(),
        ];

        foreach ($this->getAdminIds($pesaje->organizacion_id) as $adminId) {
            $this->alertaRepository->create(array_merge($base, ['user_id' => $adminId]));
        }
    }

    // ── Detección automática (llamada desde DetectarAlertasCommand) ───

    public function detectarParaOrganizacion(int $organizacionId): void
    {
        $ayer = today()->subDay();

        $this->detectarVolumenAtipico($organizacionId, $ayer);
        $this->detectarGapRegistro($organizacionId, $ayer);
        $this->detectarFrecuenciaZonaAtipica($organizacionId, $ayer);
    }

    private function detectarVolumenAtipico(int $organizacionId, Carbon $fecha): void
    {
        $config = $this->alertaRepository->getConfig($organizacionId, 'volumen_diario_atipico');
        if ($config && ! $config->activo) {
            return;
        }

        $umbralPct = $config?->umbral_valor ?? ConfigAlerta::defaults()['volumen_diario_atipico']['umbral_valor'];

        if ($this->alertaRepository->existeHoy($organizacionId, 'volumen_diario_atipico', $fecha)) {
            return;
        }

        // Toneladas del día analizado
        $toneladasDia = Pesaje::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->whereDate('created_at', $fecha)
            ->where('estado', '!=', 'Cancelado')
            ->sum('peso_neto_kg') / 1000;

        if ($toneladasDia == 0) {
            return; // sin pesajes: gap_registro lo cubre
        }

        // Promedio de los 30 días anteriores al día analizado
        $inicio30 = $fecha->copy()->subDays(30);
        $fin30 = $fecha->copy()->subDay();

        $promedioToneladas = Pesaje::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->whereDate('created_at', '>=', $inicio30->toDateString())
            ->whereDate('created_at', '<=', $fin30->toDateString())
            ->where('estado', '!=', 'Cancelado')
            ->avg('peso_neto_kg');

        if (! $promedioToneladas) {
            return; // sin historial suficiente
        }

        $diasHistorial = $this->diasConPesajes($organizacionId, $inicio30, $fin30);

        if ($diasHistorial < 5) {
            return; // historial insuficiente
        }

        $promedioTon = Pesaje::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->whereDate('created_at', '>=', $inicio30->toDateString())
            ->whereDate('created_at', '<=', $fin30->toDateString())
            ->where('estado', '!=', 'Cancelado')
            ->sum('peso_neto_kg') / 1000 / $diasHistorial;

        if ($promedioTon == 0) {
            return;
        }

        $desviacionPct = abs(($toneladasDia - $promedioTon) / $promedioTon) * 100;

        if ($desviacionPct < $umbralPct) {
            return;
        }

        $direccion = $toneladasDia > $promedioTon ? 'por encima' : 'por debajo';

        $this->createParaAdmins($organizacionId, [
            'tipo'        => 'volumen_diario_atipico',
            'titulo'      => 'Volumen diario atípico — '.$fecha->translatedFormat('d/m/Y'),
            'descripcion' => \sprintf(
                'Se recolectaron %.1f t el %s (%.0f%% %s del promedio histórico de %.1f t/día).',
                $toneladasDia,
                $fecha->translatedFormat('d/m/Y'),
                $desviacionPct,
                $direccion,
                $promedioTon,
            ),
            'fecha_deteccion' => $fecha->toDateString(),
        ]);
    }

    private function detectarGapRegistro(int $organizacionId, Carbon $fecha): void
    {
        $config = $this->alertaRepository->getConfig($organizacionId, 'gap_registro');
        if ($config && ! $config->activo) {
            return;
        }

        $umbralMinutos = (int) ($config?->umbral_valor ?? ConfigAlerta::defaults()['gap_registro']['umbral_valor']);

        // Solo días hábiles (lunes a sábado)
        if ($fecha->isSunday()) {
            return;
        }

        if ($this->alertaRepository->existeHoy($organizacionId, 'gap_registro', $fecha)) {
            return;
        }

        $pesajesDia = Pesaje::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->whereDate('created_at', $fecha->toDateString())
            ->where('estado', '!=', 'Cancelado')
            ->orderBy('created_at')
            ->pluck('created_at');

        $defaultsGap = ConfigAlerta::defaults()['gap_registro'];
        $horaInicio = $config?->hora_inicio ?: $defaultsGap['hora_inicio'];
        $horaFin = $config?->hora_fin ?: $defaultsGap['hora_fin'];

        $iniciOperativo = $fecha->copy()->setTimeFromTimeString($horaInicio);
        $finOperativo = $fecha->copy()->setTimeFromTimeString($horaFin);

        // Sin pesajes en todo el día operativo
        if ($pesajesDia->isEmpty()) {
            $this->createParaAdmins($organizacionId, [
                'tipo'            => 'gap_registro',
                'titulo'          => 'Sin actividad — '.$fecha->translatedFormat('d/m/Y'),
                'descripcion'     => "No se registraron pesajes durante el horario operativo ({$horaInicio}–{$horaFin}).",
                'fecha_deteccion' => $fecha->toDateString(),
            ]);

            return;
        }

        // Detectar gaps dentro del horario operativo
        $puntos = collect([$iniciOperativo])
            ->merge($pesajesDia->filter(fn ($t) => $t->between($iniciOperativo, $finOperativo)))
            ->push($finOperativo)
            ->values();

        for ($i = 0; $i < $puntos->count() - 1; $i++) {
            $gap = $puntos[$i]->diffInMinutes($puntos[$i + 1]);
            if ($gap >= $umbralMinutos) {
                $desde = $puntos[$i]->format('H:i');
                $hasta = $puntos[$i + 1]->format('H:i');
                $this->createParaAdmins($organizacionId, [
                    'tipo'            => 'gap_registro',
                    'titulo'          => "Sin actividad {$gap} min — ".$fecha->translatedFormat('d/m/Y'),
                    'descripcion'     => "No se registraron pesajes entre las {$desde} y las {$hasta} ({$gap} minutos).",
                    'fecha_deteccion' => $fecha->toDateString(),
                ]);
                break; // una alerta por día es suficiente
            }
        }
    }

    private function detectarFrecuenciaZonaAtipica(int $organizacionId, Carbon $fecha): void
    {
        $config = $this->alertaRepository->getConfig($organizacionId, 'frecuencia_zona_atipica');
        if ($config && ! $config->activo) {
            return;
        }

        $umbralPct = $config?->umbral_valor ?? ConfigAlerta::defaults()['frecuencia_zona_atipica']['umbral_valor'];

        $inicio30 = $fecha->copy()->subDays(30);
        $fin30 = $fecha->copy()->subDay();

        $diasHistorial = $this->diasConPesajes($organizacionId, $inicio30, $fin30);
        if ($diasHistorial < 5) {
            return;
        }

        // Pesajes del día analizado por zona
        $porZonaDia = Pesaje::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->whereDate('created_at', $fecha->toDateString())
            ->where('estado', '!=', 'Cancelado')
            ->whereNotNull('zona_id')
            ->select('zona_id', DB::raw('count(*) as total'))
            ->groupBy('zona_id')
            ->pluck('total', 'zona_id');

        if ($porZonaDia->isEmpty()) {
            return;
        }

        // Promedio histórico por zona (últimos 30 días)
        $promedioZona = Pesaje::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->whereDate('created_at', '>=', $inicio30->toDateString())
            ->whereDate('created_at', '<=', $fin30->toDateString())
            ->where('estado', '!=', 'Cancelado')
            ->whereNotNull('zona_id')
            ->select('zona_id', DB::raw('count(*) as total'))
            ->groupBy('zona_id')
            ->pluck('total', 'zona_id')
            ->map(fn ($t) => $t / $diasHistorial);

        foreach ($porZonaDia as $zonaId => $totalDia) {
            $promedio = $promedioZona[$zonaId] ?? null;
            if (! $promedio) {
                continue;
            }

            if ($this->alertaRepository->existeHoy($organizacionId, 'frecuencia_zona_atipica', $fecha, null, $zonaId)) {
                continue;
            }

            $desviacionPct = abs(($totalDia - $promedio) / $promedio) * 100;
            if ($desviacionPct < $umbralPct) {
                continue;
            }

            $direccion = $totalDia > $promedio ? 'por encima' : 'por debajo';

            $this->createParaAdmins($organizacionId, [
                'tipo'        => 'frecuencia_zona_atipica',
                'titulo'      => 'Frecuencia atípica en zona — '.$fecha->translatedFormat('d/m/Y'),
                'descripcion' => \sprintf(
                    '%d pesajes registrados (%.0f%% %s del promedio de %.1f/día en los últimos 30 días).',
                    $totalDia,
                    $desviacionPct,
                    $direccion,
                    $promedio,
                ),
                'zona_id'         => $zonaId,
                'fecha_deteccion' => $fecha->toDateString(),
            ]);
        }
    }

    private function diasConPesajes(int $organizacionId, Carbon $desde, Carbon $hasta): int
    {
        return (int) Pesaje::withoutGlobalScopes()
            ->where('organizacion_id', $organizacionId)
            ->whereDate('created_at', '>=', $desde->toDateString())
            ->whereDate('created_at', '<=', $hasta->toDateString())
            ->where('estado', '!=', 'Cancelado')
            ->distinct()
            ->count(DB::raw('CAST(created_at AS DATE)'));
    }

    // ── Config ────────────────────────────────────────────────────────

    public function getConfigConDefaults(int $organizacionId): array
    {
        $guardadas = $this->alertaRepository->getConfigPorOrg($organizacionId);
        $defaults = ConfigAlerta::defaults();
        $resultado = [];

        foreach ($defaults as $tipo => $default) {
            $guardada = $guardadas[$tipo] ?? null;
            $overrides = [
                'activo'       => $guardada ? $guardada->activo : $default['activo'],
                'umbral_valor' => $guardada ? $guardada->umbral_valor : $default['umbral_valor'],
            ];

            // El horario operativo solo aplica a los tipos que lo definen (gap_registro).
            if (\array_key_exists('hora_inicio', $default)) {
                $overrides['hora_inicio'] = $guardada?->hora_inicio ?: $default['hora_inicio'];
                $overrides['hora_fin'] = $guardada?->hora_fin ?: $default['hora_fin'];
            }

            $resultado[$tipo] = array_merge($default, $overrides);
        }

        return $resultado;
    }

    public function guardarConfig(int $organizacionId, array $data): void
    {
        foreach (ConfigAlerta::defaults() as $tipo => $default) {
            if (! isset($data[$tipo])) {
                continue;
            }

            $payload = [
                'activo'       => (bool) ($data[$tipo]['activo'] ?? false),
                'umbral_valor' => isset($data[$tipo]['umbral_valor']) && $data[$tipo]['umbral_valor'] !== ''
                    ? (float) $data[$tipo]['umbral_valor']
                    : null,
            ];

            // Horario operativo: solo se persiste si el tipo lo soporta y llegan valores.
            if (\array_key_exists('hora_inicio', $default)) {
                if (! empty($data[$tipo]['hora_inicio'])) {
                    $payload['hora_inicio'] = $data[$tipo]['hora_inicio'];
                }
                if (! empty($data[$tipo]['hora_fin'])) {
                    $payload['hora_fin'] = $data[$tipo]['hora_fin'];
                }
            }

            $this->alertaRepository->upsertConfig($organizacionId, $tipo, $payload);
        }
    }
}
