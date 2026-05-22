<?php

namespace App\Services;

use App\Models\Pesaje;
use App\Models\User;
use App\Repositories\PesajeLogRepository;
use App\Repositories\PesajeRepository;
use Illuminate\Validation\ValidationException;

class PesajeService
{
    public function __construct(
        protected PesajeRepository $pesajeRepository,
        protected PesajeLogRepository $logRepository,
    ) {}

    public function crear(array $data, User $operador): Pesaje
    {
        $vehiculo = \App\Models\Vehiculo::with('tipoVehiculo')->findOrFail($data['vehiculo_id']);

        $pesoBruto = (int) $data['peso_bruto_kg'];
        $pesaTara  = $vehiculo->tara_kg;
        $pesoNeto  = max(0, $pesoBruto - $pesaTara);

        $alerta = false;
        $tipo   = $vehiculo->tipoVehiculo;
        if ($tipo && ($pesoBruto < $tipo->peso_min_kg || $pesoBruto > $tipo->peso_max_kg)) {
            $alerta = true;
        }

        return $this->pesajeRepository->create([
            'vehiculo_id'     => $vehiculo->id,
            'operador_id'     => $operador->id,
            'tipo_servicio_id'=> $data['tipo_servicio_id'],
            'zona_id'         => $data['zona_id'],
            'turno'           => $data['turno'] ?? null,
            'peso_bruto_kg'   => $pesoBruto,
            'peso_tara_kg'    => $pesaTara,
            'peso_neto_kg'    => $pesoNeto,
            'alerta_peso'     => $alerta,
            'observaciones'   => $data['observaciones'] ?? null,
            'estado'          => 'En predio',
            'editado'         => false,
        ]);
    }

    public function marcarEgreso(Pesaje $pesaje, array $data): Pesaje
    {
        if ($pesaje->estaCerrado()) {
            throw ValidationException::withMessages(['estado' => 'El pesaje ya fue cerrado.']);
        }

        return $this->pesajeRepository->update($pesaje, [
            'estado'         => 'Cerrado',
            'hora_salida'    => now(),
            'bruto_salida_kg'=> isset($data['bruto_salida_kg']) ? (int) $data['bruto_salida_kg'] : null,
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
            if (!array_key_exists($campo, $data)) {
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

        if (!empty($cambios)) {
            if (isset($cambios['peso_bruto_kg'])) {
                $cambios['peso_neto_kg'] = max(0, (int) $cambios['peso_bruto_kg'] - $pesaje->peso_tara_kg);
            }
            $cambios['editado'] = true;
            $this->pesajeRepository->update($pesaje, $cambios);
        }

        return $pesaje->fresh();
    }
}
