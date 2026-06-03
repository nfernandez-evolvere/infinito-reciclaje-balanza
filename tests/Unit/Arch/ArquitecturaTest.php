<?php

namespace Tests\Unit\Arch;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Arch tests: verifican que el código sigue las reglas de CLAUDE.md.
 *
 * Implementados con reflexión de archivos (sin dependencias externas).
 * No tocan la base de datos — el atributo #[Test] los clasifica como Unit.
 *
 * Cuando un test falla, el mensaje lista los archivos que violan la regla,
 * así el error es accionable sin tener que buscar.
 */
class ArquitecturaTest extends TestCase
{
    // ── Controllers ───────────────────────────────────────────────────

    #[Test]
    public function ningun_controller_usa_invoke(): void
    {
        $violators = $this->findInDir('app/Http/Controllers', 'public function __invoke(');

        $this->assertEmpty(
            $violators,
            "Controllers con __invoke (CLAUDE.md: usar controller por dominio con metodos con nombre):\n- "
            .implode("\n- ", $violators)
        );
    }

    #[Test]
    public function controllers_no_llaman_a_DB_directamente(): void
    {
        // La lógica de acceso a datos va en Repositories. DB:: en controllers
        // indica lógica de negocio/datos que escapó de la capa correcta.
        $violators = $this->findInDir('app/Http/Controllers', 'DB::');

        $this->assertEmpty(
            $violators,
            "Controllers con DB:: (mover a Repository):\n- ".implode("\n- ", $violators)
        );
    }

    #[Test]
    public function controllers_no_usan_validate_inline(): void
    {
        // La validación va en Form Requests. ->validate( en un controller
        // indica que se saltó el patrón de Form Request.
        $violators = $this->findInDir('app/Http/Controllers', '->validate(');

        $this->assertEmpty(
            $violators,
            "Controllers con ->validate( (usar Form Request):\n- ".implode("\n- ", $violators)
        );
    }

    // ── Models ────────────────────────────────────────────────────────

    #[Test]
    public function models_no_dependen_de_mail(): void
    {
        // Los modelos son solo relaciones, scopes, accessors y mutators.
        // Dependencias de Mail indican lógica de negocio que pertenece a Services.
        $violators = $this->findInDir('app/Models', 'Facades\Mail');

        $this->assertEmpty(
            $violators,
            "Models con dependencia de Mail (mover a Service):\n- ".implode("\n- ", $violators)
        );
    }

    #[Test]
    public function models_no_dependen_de_http_client(): void
    {
        $violators = $this->findInDir('app/Models', 'Facades\Http');

        $this->assertEmpty(
            $violators,
            "Models con dependencia de Http (mover a Service):\n- ".implode("\n- ", $violators)
        );
    }

    // ── Form Requests ─────────────────────────────────────────────────

    #[Test]
    public function todas_las_requests_extienden_FormRequest(): void
    {
        $violators = [];

        foreach ($this->phpFilesIn('app/Http/Requests') as $file) {
            $content = $file->getContents();

            // Omitir archivos que no declaran una clase (son solo includes, etc.)
            if (! str_contains($content, 'class ')) {
                continue;
            }

            if (! str_contains($content, 'extends FormRequest')
                && ! str_contains($content, 'extends StoreReporteProgramadoRequest')
            ) {
                $violators[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty(
            $violators,
            "Requests que no extienden FormRequest:\n- ".implode("\n- ", $violators)
        );
    }

    // ── Namespaces / capas ────────────────────────────────────────────

    #[Test]
    public function services_viven_en_App_Services(): void
    {
        $violators = [];

        foreach ($this->phpFilesIn('app/Services') as $file) {
            $content = $file->getContents();
            if (str_contains($content, 'namespace ') && ! str_contains($content, 'namespace App\Services')) {
                $violators[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty(
            $violators,
            "Services fuera de App\Services:\n- ".implode("\n- ", $violators)
        );
    }

    #[Test]
    public function repositories_viven_en_App_Repositories(): void
    {
        $violators = [];

        foreach ($this->phpFilesIn('app/Repositories') as $file) {
            $content = $file->getContents();
            if (str_contains($content, 'namespace ') && ! str_contains($content, 'namespace App\Repositories')) {
                $violators[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty(
            $violators,
            "Repositories fuera de App\Repositories:\n- ".implode("\n- ", $violators)
        );
    }

    // ── Tests: convenciones ───────────────────────────────────────────

    #[Test]
    public function los_test_de_integracion_usan_RefreshDatabase(): void
    {
        // Los tests de Integration tocan la DB — deben usar RefreshDatabase
        // para no contaminar entre tests.
        $violators = [];

        foreach ($this->phpFilesIn('tests/Integration') as $file) {
            $content = $file->getContents();
            if (str_contains($content, 'class ')
                && ! str_contains($content, 'RefreshDatabase')
            ) {
                $violators[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty(
            $violators,
            "Integration tests sin RefreshDatabase (pueden contaminar entre tests):\n- "
            .implode("\n- ", $violators)
        );
    }

    #[Test]
    public function los_unit_tests_puros_no_usan_RefreshDatabase(): void
    {
        // Los tests en Unit/ deben ser puros (sin DB). Si necesitan DB
        // deberían estar en Integration/.
        $violators = [];

        foreach ($this->phpFilesIn('tests/Unit') as $file) {
            // Excluir esta misma carpeta Arch/
            if (str_contains($file->getPathname(), 'Arch')) {
                continue;
            }

            $content = $file->getContents();
            if (str_contains($content, 'RefreshDatabase')) {
                $violators[] = $file->getRelativePathname();
            }
        }

        $this->assertEmpty(
            $violators,
            "Unit tests con RefreshDatabase (mover a tests/Integration/):\n- "
            .implode("\n- ", $violators)
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /** Retorna los pathnames relativos de archivos que contienen $pattern. */
    private function findInDir(string $dir, string $pattern): array
    {
        $violators = [];

        foreach ($this->phpFilesIn($dir) as $file) {
            if (str_contains($file->getContents(), $pattern)) {
                $violators[] = $file->getRelativePathname();
            }
        }

        return $violators;
    }

    private function phpFilesIn(string $dir): Finder
    {
        return (new Finder)->files()->in(base_path($dir))->name('*.php');
    }
}
