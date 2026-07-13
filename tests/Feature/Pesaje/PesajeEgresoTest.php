<?php

namespace Tests\Feature\Pesaje;

use App\Models\Pesaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PesajeEgresoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function operador_marks_egreso_and_is_redirected_to_historial(): void
    {
        $pesaje = Pesaje::factory()->enPredio()->create();

        $this->actingAs($this->operador())
            ->post(route('pesajes.egreso', $pesaje))
            ->assertRedirect(route('historial'));

        $this->assertDatabaseHas('pesajes', ['id' => $pesaje->id, 'estado' => 'Cerrado']);
    }

    #[Test]
    public function admin_marks_egreso_and_is_redirected_to_admin_index(): void
    {
        $pesaje = Pesaje::factory()->enPredio()->create();

        $this->actingAs($this->admin())
            ->post(route('pesajes.egreso', $pesaje))
            ->assertRedirect(route('admin.pesajes.index'));

        $this->assertDatabaseHas('pesajes', ['id' => $pesaje->id, 'estado' => 'Cerrado']);
    }

    #[Test]
    public function egreso_saves_bruto_salida_when_provided(): void
    {
        $pesaje = Pesaje::factory()->enPredio()->create();

        $this->actingAs($this->operador())
            ->post(route('pesajes.egreso', $pesaje), ['bruto_salida_kg' => 9000]);

        $this->assertDatabaseHas('pesajes', ['id' => $pesaje->id, 'bruto_salida_kg' => 9000]);
    }

    #[Test]
    public function egreso_validates_bruto_salida_integer_min_1(): void
    {
        $pesaje = Pesaje::factory()->enPredio()->create();

        $this->actingAs($this->operador())
            ->post(route('pesajes.egreso', $pesaje), ['bruto_salida_kg' => 0])
            ->assertSessionHasErrors('bruto_salida_kg');
    }

    #[Test]
    public function egreso_acepta_bruto_salida_en_el_limite_minimo(): void
    {
        // Borde exacto de min:1 — 1 kg es válido (0 falla, test anterior).
        $pesaje = Pesaje::factory()->enPredio()->create();

        $this->actingAs($this->operador())
            ->post(route('pesajes.egreso', $pesaje), ['bruto_salida_kg' => 1])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pesajes', [
            'id'              => $pesaje->id,
            'bruto_salida_kg' => 1,
            'estado'          => 'Cerrado',
        ]);
    }

    #[Test]
    public function egreso_on_closed_pesaje_fails_with_validation_error(): void
    {
        $pesaje = Pesaje::factory()->create(['estado' => 'Cerrado']);

        $this->actingAs($this->operador())
            ->post(route('pesajes.egreso', $pesaje))
            ->assertSessionHasErrors('estado');

        $this->assertDatabaseHas('pesajes', ['id' => $pesaje->id, 'estado' => 'Cerrado']);
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $pesaje = Pesaje::factory()->enPredio()->create();

        $this->post(route('pesajes.egreso', $pesaje))
            ->assertRedirect(route('login'));
    }
}
