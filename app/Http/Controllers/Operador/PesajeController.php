<?php

namespace App\Http\Controllers\Operador;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesajeRequest;
use App\Http\Requests\UpdatePesajeRequest;
use App\Models\Pesaje;
use App\Models\TipoServicio;
use App\Models\ZonaServicio;
use App\Models\ZonaServicioTurno;
use App\Services\PesajeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PesajeController extends Controller
{
    public function __construct(protected PesajeService $pesajeService) {}

    public function show(Pesaje $pesaje): View
    {
        $pesaje->load(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona', 'operador']);
        return view('modules.operador.pesaje-detalle', compact('pesaje'));
    }

    public function edit(Pesaje $pesaje): View
    {
        $pesaje->load(['vehiculo.tipoVehiculo', 'tipoServicio', 'zona']);

        $servicios = TipoServicio::activos()->with('tipoVehiculoSugerido')->get();

        $v = $pesaje->vehiculo;
        $vehiculoJs = [
            'id'       => $v->id,
            'patente'  => $v->patente,
            'interno'  => $v->numero_interno,
            'tara'     => $v->tara_kg,
            'tipo'     => $v->tipoVehiculo?->nombre,
            'titular'  => $v->titular,
            'peso_min' => $v->tipoVehiculo?->peso_min_kg,
            'peso_max' => $v->tipoVehiculo?->peso_max_kg,
        ];

        $servicio = $pesaje->tipoServicio;

        $zonaServicios = ZonaServicio::with('zona')
            ->where('tipo_servicio_id', $servicio->id)
            ->whereHas('zona', fn ($q) => $q->where('activo', true))
            ->get();

        $zonaIds = $zonaServicios->pluck('zona_id');

        $turnosPorZona = ZonaServicioTurno::where('tipo_servicio_id', $servicio->id)
            ->whereIn('zona_id', $zonaIds)
            ->get()
            ->groupBy(fn ($t) => (string) $t->zona_id)
            ->map(fn ($ts) => $ts->pluck('turno')->values()->all());

        $zonasDisponibles = $zonaServicios->map(fn ($zs) => [
            'id'     => $zs->zona->id,
            'nombre' => $zs->zona->nombre,
            'turnos' => $turnosPorZona[(string) $zs->zona_id] ?? [],
        ])->values();

        $zonaActual = $zonasDisponibles->firstWhere('id', $pesaje->zona_id);

        $initial = [
            'vehiculo'          => $vehiculoJs,
            'servicioId'        => $servicio->id,
            'servicioNombre'    => $servicio->nombre,
            'tipoSugerido'      => $servicio->tipoVehiculoSugerido?->nombre,
            'zonasDisponibles'  => $zonasDisponibles->toArray(),
            'zonaId'            => $pesaje->zona_id,
            'zonaNombre'        => $pesaje->zona->nombre,
            'turnosDisponibles' => $zonaActual['turnos'] ?? [],
            'turno'             => $pesaje->turno ?? '',
            'pesoBruto'         => $pesaje->peso_bruto_kg,
            'observaciones'     => $pesaje->observaciones ?? '',
        ];

        return view('modules.operador.pesaje-editar', compact('pesaje', 'servicios', 'initial'));
    }

    public function store(StorePesajeRequest $request): RedirectResponse
    {
        $pesaje = $this->pesajeService->crear($request->validated(), auth()->user());
        return redirect()->route('pesajes.show', $pesaje);
    }

    public function update(UpdatePesajeRequest $request, Pesaje $pesaje): RedirectResponse
    {
        $this->pesajeService->editar($pesaje, $request->validated(), auth()->user());

        $pesaje->loadMissing('vehiculo');

        return redirect()->route('historial')
            ->with('toast', [
                'message'     => 'Cambios guardados',
                'description' => 'Se actualizó el pesaje de ' . $pesaje->vehiculo->patente . '.',
                'variant'     => 'success',
            ]);
    }
}
