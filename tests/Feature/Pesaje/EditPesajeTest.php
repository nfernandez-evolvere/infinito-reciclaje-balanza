<?php

namespace Tests\Feature\Pesaje;

use App\Models\Pesaje;
use App\Models\Zona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditPesajeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function update_requires_motivo(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->actingAs($this->operador())
            ->put(route('pesajes.update', $pesaje), ['zona_id' => Zona::factory()->create()->id])
            ->assertSessionHasErrors('motivo');
    }

    #[Test]
    public function editing_bruto_recalculates_neto_and_appears_in_log(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'peso_tara_kg'  => 8000,
            'peso_neto_kg'  => 12000,
            'editado'       => false,
        ]);

        $this->actingAs($this->operador())
            ->put(route('pesajes.update', $pesaje), [
                'peso_bruto_kg' => 25000,
                'motivo'        => 'Corrección del peso bruto.',
            ])
            ->assertRedirect(route('historial'));

        $this->assertDatabaseHas('pesajes', [
            'id'            => $pesaje->id,
            'peso_bruto_kg' => 25000,
            'peso_neto_kg'  => 17000,
            'editado'       => true,
        ]);
        $this->assertDatabaseHas('pesajes_log', ['pesaje_id' => $pesaje->id, 'campo' => 'peso_bruto_kg']);
    }

    #[Test]
    public function editing_only_zona_does_not_touch_the_rest(): void
    {
        $pesaje = Pesaje::factory()->create([
            'peso_bruto_kg' => 20000,
            'turno'         => 'Mañana',
        ]);
        $nuevaZona = Zona::factory()->create();

        $this->actingAs($this->operador())
            ->put(route('pesajes.update', $pesaje), [
                'zona_id' => $nuevaZona->id,
                'motivo'  => 'Origen mal cargado.',
            ])
            ->assertRedirect(route('historial'));

        $this->assertDatabaseHas('pesajes', [
            'id'            => $pesaje->id,
            'zona_id'       => $nuevaZona->id,
            'peso_bruto_kg' => 20000,
            'turno'         => 'Mañana',
        ]);
        // Solo se registra el campo que cambió.
        $this->assertDatabaseCount('pesajes_log', 1);
        $this->assertDatabaseHas('pesajes_log', ['pesaje_id' => $pesaje->id, 'campo' => 'zona_id']);
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $pesaje = Pesaje::factory()->create();

        $this->put(route('pesajes.update', $pesaje), ['motivo' => 'algo'])
            ->assertRedirect(route('login'));
    }
}
