<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // ── forgot-password (PasswordResetLinkController) ─────────────────

    #[Test]
    public function forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    #[Test]
    public function forgot_password_envia_link_a_email_existente(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'usuario@test.com']);

        $this->post(route('password.email'), ['email' => 'usuario@test.com'])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    #[Test]
    public function forgot_password_no_revela_si_el_email_no_existe(): void
    {
        // Por seguridad: el error debe estar en 'email', no en un mensaje genérico
        // que confirme si el email existe o no. El controller usa INVALID_USER.
        $response = $this->post(route('password.email'), ['email' => 'inexistente@test.com']);

        $response->assertSessionHasErrors('email');
        // No debe haber enviado ninguna notificación.
        Notification::fake();
        Notification::assertNothingSent();
    }

    #[Test]
    public function forgot_password_validates_email_required(): void
    {
        $this->post(route('password.email'), ['email' => ''])
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function forgot_password_validates_email_format(): void
    {
        $this->post(route('password.email'), ['email' => 'no-es-email'])
            ->assertSessionHasErrors('email');
    }

    // ── reset-password (NewPasswordController) ────────────────────────

    #[Test]
    public function reset_password_page_renders_with_token(): void
    {
        $this->get(route('password.reset', ['token' => 'fake-token']))
            ->assertOk();
    }

    #[Test]
    public function reset_password_cambia_la_contrasena_y_redirige_al_login(): void
    {
        $user = User::factory()->create(['email' => 'reset@test.com']);
        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token'                 => $token,
            'email'                 => 'reset@test.com',
            'password'              => 'NuevaPass@123',
            'password_confirmation' => 'NuevaPass@123',
        ])->assertRedirect(route('login'));

        // La contraseña fue cambiada: el hash ya no coincide con el anterior.
        $this->assertTrue(Hash::check('NuevaPass@123', $user->fresh()->password));
    }

    #[Test]
    public function reset_password_con_token_invalido_devuelve_error_en_email(): void
    {
        User::factory()->create(['email' => 'reset@test.com']);

        $this->post(route('password.store'), [
            'token'                 => 'token-invalido',
            'email'                 => 'reset@test.com',
            'password'              => 'NuevaPass@123',
            'password_confirmation' => 'NuevaPass@123',
        ])->assertSessionHasErrors('email');
    }

    #[Test]
    public function reset_password_validates_password_required(): void
    {
        $this->post(route('password.store'), [
            'token'                 => 'tok',
            'email'                 => 'a@b.com',
            'password'              => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors('password');
    }

    #[Test]
    public function reset_password_validates_passwords_must_match(): void
    {
        $this->post(route('password.store'), [
            'token'                 => 'tok',
            'email'                 => 'a@b.com',
            'password'              => 'NuevaPass@123',
            'password_confirmation' => 'Diferente@456',
        ])->assertSessionHasErrors('password');
    }
}
