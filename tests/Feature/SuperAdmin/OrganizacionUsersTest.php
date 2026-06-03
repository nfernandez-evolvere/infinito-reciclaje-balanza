<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Organizacion;
use App\Models\User;
use App\Notifications\AdminInvitacionNotification;
use App\Notifications\AdminNuevaOrganizacionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrganizacionUsersTest extends TestCase
{
    use RefreshDatabase;

    // ── addUser ───────────────────────────────────────────────────────

    #[Test]
    public function addUser_crea_usuario_nuevo_y_lo_adjunta_a_la_org(): void
    {
        Notification::fake();
        $org = Organizacion::factory()->create();

        $this->actingAs($this->superAdmin())
            ->postJson(route('super.organizaciones.addUser', $org), [
                'email' => 'nuevo@municipio.gob.ar',
                'name'  => 'Roberto García',
            ])
            ->assertOk()
            ->assertJsonPath('user.email', 'nuevo@municipio.gob.ar');

        $user = User::where('email', 'nuevo@municipio.gob.ar')->first();
        $this->assertNotNull($user);
        $this->assertTrue($org->users()->whereKey($user->id)->exists());

        // Usuario nuevo → recibe invitación con link de reset de contraseña.
        Notification::assertSentTo($user, AdminInvitacionNotification::class);
    }

    #[Test]
    public function addUser_adjunta_usuario_existente_sin_duplicar(): void
    {
        Notification::fake();
        $org = Organizacion::factory()->create();
        $existente = User::factory()->create(['email' => 'existente@test.com']);

        $this->actingAs($this->superAdmin())
            ->postJson(route('super.organizaciones.addUser', $org), [
                'email' => 'existente@test.com',
            ])
            ->assertOk();

        // Debe adjuntarse, no crearse un segundo usuario.
        $this->assertSame(1, User::where('email', 'existente@test.com')->count());
        $this->assertTrue($org->users()->whereKey($existente->id)->exists());

        // Usuario existente → recibe notificación de nueva organización (no reset de contraseña).
        Notification::assertSentTo($existente, AdminNuevaOrganizacionNotification::class);
        Notification::assertNotSentTo($existente, AdminInvitacionNotification::class);
    }

    #[Test]
    public function addUser_rechaza_super_admin(): void
    {
        $org = Organizacion::factory()->create();
        $superAdmin = $this->superAdmin();

        $this->actingAs($this->superAdmin())
            ->postJson(route('super.organizaciones.addUser', $org), [
                'email' => $superAdmin->email,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Los super admins no pueden pertenecer a una organización.');
    }

    #[Test]
    public function addUser_rechaza_usuario_ya_en_la_org(): void
    {
        $org = Organizacion::factory()->create();
        $user = User::factory()->create();
        $org->users()->attach($user->id);

        $this->actingAs($this->superAdmin())
            ->postJson(route('super.organizaciones.addUser', $org), [
                'email' => $user->email,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'El usuario ya pertenece a esta organización.');
    }

    #[Test]
    public function addUser_validates_email_required(): void
    {
        $org = Organizacion::factory()->create();

        $this->actingAs($this->superAdmin())
            ->postJson(route('super.organizaciones.addUser', $org), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    // ── removeUser ────────────────────────────────────────────────────

    #[Test]
    public function removeUser_desvincula_usuario_sin_borrarlo(): void
    {
        $org = Organizacion::factory()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $org->users()->attach([$userA->id, $userB->id]);

        $this->actingAs($this->superAdmin())
            ->deleteJson(route('super.organizaciones.removeUser', [$org, $userA]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertFalse($org->users()->whereKey($userA->id)->exists());
        $this->assertDatabaseHas('users', ['id' => $userA->id]);
    }

    #[Test]
    public function removeUser_rechaza_si_la_org_quedaria_sin_usuarios(): void
    {
        $org = Organizacion::factory()->create();
        $unico = User::factory()->create();
        $org->users()->attach($unico->id);

        $this->actingAs($this->superAdmin())
            ->deleteJson(route('super.organizaciones.removeUser', [$org, $unico]))
            ->assertStatus(422)
            ->assertJsonPath('message', 'La organización debe tener al menos un usuario.');

        $this->assertTrue($org->users()->whereKey($unico->id)->exists());
    }

    #[Test]
    public function removeUser_rechaza_usuario_que_no_pertenece_a_la_org(): void
    {
        $org = Organizacion::factory()->create();
        $externo = User::factory()->create();

        $this->actingAs($this->superAdmin())
            ->deleteJson(route('super.organizaciones.removeUser', [$org, $externo]))
            ->assertStatus(422)
            ->assertJsonPath('message', 'El usuario no pertenece a esta organización.');
    }

    // ── resetUserPassword ─────────────────────────────────────────────

    #[Test]
    public function resetUserPassword_ok_para_usuario_de_la_org(): void
    {
        Notification::fake();
        $org = Organizacion::factory()->create();
        $user = User::factory()->create();
        $org->users()->attach($user->id);

        $this->actingAs($this->superAdmin())
            ->postJson(route('super.organizaciones.resetUserPassword', [$org, $user]))
            ->assertOk()
            ->assertJsonPath('success', true);

        // Debe haber enviado el link de reset al usuario.
        Notification::assertSentTo($user, AdminInvitacionNotification::class);
    }

    #[Test]
    public function resetUserPassword_rechaza_usuario_fuera_de_la_org(): void
    {
        $org = Organizacion::factory()->create();
        $externo = User::factory()->create();

        $this->actingAs($this->superAdmin())
            ->postJson(route('super.organizaciones.resetUserPassword', [$org, $externo]))
            ->assertStatus(422)
            ->assertJsonPath('message', 'El usuario no pertenece a esta organización.');
    }

    // ── searchUsers ───────────────────────────────────────────────────

    #[Test]
    public function searchUsers_retorna_coincidencias_por_nombre(): void
    {
        User::factory()->create(['name' => 'Roberto García', 'email' => 'roberto@test.com']);
        User::factory()->create(['name' => 'Ignacio López',  'email' => 'nacho@test.com']);

        $this->actingAs($this->superAdmin())
            ->getJson(route('super.usuarios.search', ['q' => 'Roberto']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.email', 'roberto@test.com');
    }

    #[Test]
    public function searchUsers_retorna_vacio_con_menos_de_2_caracteres(): void
    {
        $this->actingAs($this->superAdmin())
            ->getJson(route('super.usuarios.search', ['q' => 'R']))
            ->assertOk()
            ->assertExactJson([]);
    }
}
