<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Cambio de estado de un reporte (en_revision | enviado | fallido) empujado al
 * canal privado del dueño. ShouldBroadcastNow: se emite de forma síncrona desde
 * dentro del job de la cola — no se re-encola detrás del mismo worker.
 *
 * El payload viaja "chico": id, estado, label, toast y datos mínimos de la
 * alerta. Nunca se transmite el snapshot del reporte (pesado y sensible).
 */
class ReporteEstadoActualizado implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly int $userId,
        public readonly array $payload,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("user.{$this->userId}.reportes")];
    }

    public function broadcastAs(): string
    {
        return 'reporte.estado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
