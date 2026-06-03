<?php

namespace Tests\Feature\Pesaje;

use App\Models\Pesaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CancelPesajeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cancel_requires_motivo(): void
    {
        $pesaje = Pesaje::factory()->create(['estado' => 'En predio']);

        $this->actingAs($this->operador())
            ->patch(route('pesajes.cancelar', $pesaje), [])
            ->assertSessionHasErrors('motivo');
    }

    #[Test]
    public function cancel_validates_motivo_min_5(): void
    {
        $pesaje = Pesaje::factory()->create(['estado' => 'En predio']);

        $this->actingAs($this->operador())
            ->patch(route('pesajes.cancelar', $pesaje), ['motivo' => 'abc'])
            ->assertSessionHasErrors('motivo');
    }

    #[Test]
    public function cancel_validates_motivo_max_500(): void
    {
        $pesaje = Pesaje::factory()->create(['estado' => 'En predio']);

        $this->actingAs($this->operador())
            ->patch(route('pesajes.cancelar', $pesaje), ['motivo' => str_repeat('a', 501)])
            ->assertSessionHasErrors('motivo');
    }

    #[Test]
    public function operador_cancels_pesaje_logs_and_redirects(): void
    {
        $pesaje = Pesaje::factory()->create(['estado' => 'En predio']);

        $this->actingAs($this->operador())
            ->patch(route('pesajes.cancelar', $pesaje), ['motivo' => 'Carga duplicada por error.'])
            ->assertRedirect(route('historial'));

        $this->assertDatabaseHas('pesajes', ['id' => $pesaje->id, 'estado' => 'Cancelado']);
        $this->assertDatabaseHas('pesajes_log', [
            'pesaje_id'   => $pesaje->id,
            'campo'       => 'estado',
            'valor_nuevo' => 'Cancelado',
        ]);
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $pesaje = Pesaje::factory()->create(['estado' => 'En predio']);

        $this->patch(route('pesajes.cancelar', $pesaje), ['motivo' => 'Carga duplicada por error.'])
            ->assertRedirect(route('login'));
    }
}
