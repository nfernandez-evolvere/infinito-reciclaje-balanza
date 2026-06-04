<?php

namespace App\Services;

use App\Models\Pesaje;
use App\Models\User;
use App\Models\Vehiculo;
use App\Repositories\PesajeLogRepository;
use App\Repositories\PesajeRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PesajeService
{
    public function __construct(
        protected PesajeRepository $pesajeRepository,
        protected PesajeLogRepository $logRepository,
        protected AlertaService $alertaService,
    ) {}

    public function crear(array $data, User $operador): Pesaje
    {
        $vehiculo = Vehiculo::with('tipoVehiculo')->findOrFail($data['vehiculo_id']);

        $pesoBruto = (int) $data['peso_bruto_kg'];
        $pesaTara = $vehiculo->tara_kg;
        $pesoNeto = max(0, $pesoBruto - $pesaTara);

        $alerta = false;
        $tipo = $vehiculo->tipoVehiculo;
        if ($tipo && ($pesoBruto < $tipo->peso_min_kg || $pesoBruto > $tipo->peso_max_kg)) {
            $alerta = true;
        }

        $pesaje = $this->pesajeRepository->create([
            'vehiculo_id'      => $vehiculo->id,
            'operador_id'      => $operador->id,
            'tipo_servicio_id' => $data['tipo_servicio_id'],
            'zona_id'          => $data['zona_id'],
            'turno'            => $data['turno'] ?? null,
            'peso_bruto_kg'    => $pesoBruto,
            'peso_tara_kg'     => $pesaTara,
            'peso_neto_kg'     => $pesoNeto,
            'alerta_peso'      => $alerta,
            'observaciones'    => $data['observaciones'] ?? null,
            'estado'           => 'En predio',
            'editado'          => false,
        ]);

        if ($alerta) {
            $pesaje->load('vehiculo.tipoVehiculo');
            $this->alertaService->registrarPesoFueraRango($pesaje);
        }

        return $pesaje;
    }

    public function marcarEgreso(Pesaje $pesaje, array $data): Pesaje
    {
        if ($pesaje->estaCerrado()) {
            throw ValidationException::withMessages(['estado' => 'El pesaje ya fue cerrado.']);
        }

        return $this->pesajeRepository->update($pesaje, [
            'estado'          => 'Cerrado',
            'hora_salida'     => now(),
            'bruto_salida_kg' => isset($data['bruto_salida_kg']) ? (int) $data['bruto_salida_kg'] : null,
        ]);
    }

    public function editar(Pesaje $pesaje, array $data, User $usuario): Pesaje
    {
        $motivo = $data['motivo'] ?? '';
        if (empty(trim($motivo))) {
            throw ValidationException::withMessages(['motivo' => 'Describí el motivo antes de guardar.']);
        }

        $campos = ['peso_bruto_kg', 'zona_id', 'tipo_servicio_id', 'turno', 'observaciones'];
        $cambios = [];

        foreach ($campos as $campo) {
            if (! array_key_exists($campo, $data)) {
                continue;
            }
            $nuevo = $data[$campo];
            $anterior = $pesaje->$campo;
            if ((string) $nuevo !== (string) $anterior) {
                $this->logRepository->create([
                    'pesaje_id'      => $pesaje->id,
                    'campo'          => $campo,
                    'valor_anterior' => (string) $anterior,
                    'valor_nuevo'    => (string) $nuevo,
                    'motivo'         => $motivo,
                    'usuario_id'     => $usuario->id,
                ]);
                $cambios[$campo] = $nuevo;
            }
        }

        if (! empty($cambios)) {
            if (isset($cambios['peso_bruto_kg'])) {
                $cambios['peso_neto_kg'] = max(0, (int) $cambios['peso_bruto_kg'] - $pesaje->peso_tara_kg);
            }
            $cambios['editado'] = true;
            $this->pesajeRepository->update($pesaje, $cambios);
        }

        return $pesaje->fresh();
    }

    public function cancelar(Pesaje $pesaje, array $data, User $usuario): Pesaje
    {
        if ($pesaje->estaCancelado()) {
            throw ValidationException::withMessages(['estado' => 'El pesaje ya fue cancelado.']);
        }

        $this->logRepository->create([
            'pesaje_id'      => $pesaje->id,
            'campo'          => 'estado',
            'valor_anterior' => $pesaje->estado,
            'valor_nuevo'    => 'Cancelado',
            'motivo'         => $data['motivo'],
            'usuario_id'     => $usuario->id,
        ]);

        return $this->pesajeRepository->update($pesaje, [
            'estado'             => 'Cancelado',
            'cancelado_por_id'   => $usuario->id,
            'cancelado_at'       => now(),
            'motivo_cancelacion' => $data['motivo'],
        ]);
    }

    public function exportarCsv(Collection $pesajes, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($pesajes) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID', 'Entrada', 'Salida', 'Estado',
                'Patente', 'Tipo vehículo', 'Servicio', 'Origen',
                'Operario', 'Bruto (kg)', 'Tara (kg)', 'Neto (kg)',
                'Alerta peso', 'Editado',
            ]);

            foreach ($pesajes as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->created_at->format('d/m/Y H:i'),
                    $p->hora_salida?->format('d/m/Y H:i') ?? '',
                    $p->estado,
                    $p->vehiculo->patente,
                    $p->vehiculo->tipoVehiculo?->nombre ?? '',
                    $p->tipoServicio->nombre,
                    $p->zona->nombre,
                    $p->operador->name,
                    $p->peso_bruto_kg,
                    $p->peso_tara_kg,
                    $p->peso_neto_kg,
                    $p->alerta_peso ? 'Sí' : 'No',
                    $p->editado ? 'Sí' : 'No',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
