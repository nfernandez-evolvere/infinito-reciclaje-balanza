<?php

namespace Tests\Feature\Reporte;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Autorización del canal privado user.{id}.reportes: cada usuario solo puede
 * suscribirse a SU canal. Se usa un broadcaster pusher-compatible en memoria
 * (firma offline) para ejercitar la lógica real de routes/channels.php.
 */
class NotificacionesCanalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // phpunit fuerza BROADCAST_CONNECTION=null (sin auth de canales). Para
        // testear la autorización montamos un driver pusher con credenciales de
        // prueba y re-registramos los canales reales contra ese driver.
        config([
            'broadcasting.default'                    => 'pusher',
            'broadcasting.connections.pusher.driver'  => 'pusher',
            'broadcasting.connections.pusher.key'     => 'test-key',
            'broadcasting.connections.pusher.secret'  => 'test-secret',
            'broadcasting.connections.pusher.app_id'  => 'test-app',
            'broadcasting.connections.pusher.options' => ['cluster' => 'mt1', 'useTLS' => false],
        ]);

        require base_path('routes/channels.php');
    }

    #[Test]
    public function a_user_can_authorize_their_own_reportes_channel(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->postJson('/broadcasting/auth', [
            'socket_id'    => '1234.5678',
            'channel_name' => "private-user.{$user->id}.reportes",
        ]);

        $response->assertOk();
        $this->assertArrayHasKey('auth', $response->json());
    }

    #[Test]
    public function a_user_cannot_authorize_another_users_reportes_channel(): void
    {
        $user = $this->admin();
        $otro = $this->admin();

        $response = $this->actingAs($user)->postJson('/broadcasting/auth', [
            'socket_id'    => '1234.5678',
            'channel_name' => "private-user.{$otro->id}.reportes",
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function a_guest_cannot_authorize_the_reportes_channel(): void
    {
        $response = $this->postJson('/broadcasting/auth', [
            'socket_id'    => '1234.5678',
            'channel_name' => 'private-user.1.reportes',
        ]);

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }
}
