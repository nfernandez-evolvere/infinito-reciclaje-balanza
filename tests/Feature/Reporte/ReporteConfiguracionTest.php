<?php

namespace Tests\Feature\Reporte;

use App\Models\ReporteConfiguracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteConfiguracionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'municipalidad_nombre'        => 'Municipalidad de Corrientes',
            'intro_empresa'               => null,
            'ai_enabled'                  => false,
            'tipo_informe_mensual_activo' => true,
            'tipo_alertas_activo'         => false,
        ], $overrides);
    }

    // ── Acceso ────────────────────────────────────────────────────────

    #[Test]
    public function solo_admin_puede_actualizar_configuracion(): void
    {
        $this->actingAs($this->operador())
            ->put(route('admin.reportes.configuracion.update'), $this->payload())
            ->assertForbidden();
    }

    #[Test]
    public function guest_es_redirigido_al_login(): void
    {
        $this->put(route('admin.reportes.configuracion.update'), $this->payload())
            ->assertRedirect(route('login'));
    }

    // ── updateConfiguracion ───────────────────────────────────────────

    #[Test]
    public function crea_configuracion_si_no_existia_y_persiste_datos(): void
    {
        $this->assertDatabaseCount('reporte_configuraciones', 0);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.configuracion.update'), $this->payload([
                'municipalidad_nombre'        => 'Municipalidad X',
                'tipo_informe_mensual_activo' => true,
                'tipo_alertas_activo'         => false,
            ]))
            ->assertRedirect(route('admin.reportes.index', ['tab' => 'configuracion']));

        $this->assertDatabaseHas('reporte_configuraciones', [
            'municipalidad_nombre'        => 'Municipalidad X',
            'tipo_informe_mensual_activo' => true,
            'tipo_alertas_activo'         => false,
        ]);
    }

    #[Test]
    public function actualiza_configuracion_existente_sin_duplicar(): void
    {
        ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Nombre Viejo',
            'ai_enabled'           => false,
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.configuracion.update'), $this->payload([
                'municipalidad_nombre' => 'Nombre Nuevo',
            ]));

        $this->assertDatabaseCount('reporte_configuraciones', 1);
        $this->assertDatabaseHas('reporte_configuraciones', ['municipalidad_nombre' => 'Nombre Nuevo']);
        $this->assertDatabaseMissing('reporte_configuraciones', ['municipalidad_nombre' => 'Nombre Viejo']);
    }

    #[Test]
    public function no_sobreescribe_api_key_si_viene_vacia(): void
    {
        // La api_key está encrypted; en DB no se puede comparar directamente.
        // Verificamos el comportamiento a través del modelo.
        $config = ReporteConfiguracion::create([
            'municipalidad_nombre' => 'Test',
            'ai_enabled'           => true,
            'ai_api_key'           => 'clave-original',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.configuracion.update'), $this->payload([
                'ai_enabled' => true,
                'ai_api_key' => '',   // vacía → no debe sobreescribir
            ]));

        // Si la clave se hubiera borrado, al leerla sería null.
        $this->assertNotNull($config->fresh()->ai_api_key);
    }

    #[Test]
    public function validates_municipalidad_nombre_required(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.reportes.configuracion.update'), $this->payload([
                'municipalidad_nombre' => '',
            ]))
            ->assertSessionHasErrors('municipalidad_nombre');
    }

    #[Test]
    public function validates_municipalidad_nombre_max_200(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.reportes.configuracion.update'), $this->payload([
                'municipalidad_nombre' => str_repeat('a', 201),
            ]))
            ->assertSessionHasErrors('municipalidad_nombre');
    }

    #[Test]
    public function acepta_municipalidad_nombre_en_el_limite_de_longitud(): void
    {
        // Borde exacto del lado válido: max:200 acepta exactamente 200 chars.
        $nombre = str_repeat('a', 200);

        $this->actingAs($this->admin())
            ->put(route('admin.reportes.configuracion.update'), $this->payload([
                'municipalidad_nombre' => $nombre,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reporte_configuraciones', ['municipalidad_nombre' => $nombre]);
    }
}
