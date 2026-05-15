<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    #[Test]
    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $user = new User(['role' => 'admin']);
        $this->assertTrue($user->isAdmin());
    }

    #[Test]
    public function test_is_operador_returns_true_for_operador_role(): void
    {
        $user = new User(['role' => 'operador']);
        $this->assertTrue($user->isOperador());
    }

    #[Test]
    public function test_is_admin_returns_false_for_operador_role(): void
    {
        $user = new User(['role' => 'operador']);
        $this->assertFalse($user->isAdmin());
    }

    #[Test]
    public function test_onboarding_visto_defaults_to_false(): void
    {
        $user = User::factory()->make();
        $this->assertFalse($user->onboarding_visto);
    }
}
