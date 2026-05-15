<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GateTest extends TestCase
{
    #[Test]
    public function test_operador_can_record_weighing(): void
    {
        $user = User::factory()->make(['role' => 'operador']);
        $this->assertTrue(Gate::forUser($user)->allows('record-weighing'));
    }

    #[Test]
    public function test_operador_cannot_manage_masters(): void
    {
        $user = User::factory()->make(['role' => 'operador']);
        $this->assertFalse(Gate::forUser($user)->allows('manage-masters'));
    }

    #[Test]
    public function test_admin_can_view_dashboard(): void
    {
        $user = User::factory()->make(['role' => 'admin']);
        $this->assertTrue(Gate::forUser($user)->allows('view-dashboard'));
    }

    #[Test]
    public function test_admin_can_manage_masters(): void
    {
        $user = User::factory()->make(['role' => 'admin']);
        $this->assertTrue(Gate::forUser($user)->allows('manage-masters'));
    }

    #[Test]
    public function test_admin_cannot_record_weighing(): void
    {
        $user = User::factory()->make(['role' => 'admin']);
        $this->assertFalse(Gate::forUser($user)->allows('record-weighing'));
    }
}
