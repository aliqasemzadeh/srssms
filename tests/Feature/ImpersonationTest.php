<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\ImpersonationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_impersonate_user_and_leave(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test('pages::panels.administrator.user-management.user.index')
            ->call('impersonate', $target->id)
            ->assertRedirect(route('panels.user.dashboard.index'));

        $this->assertAuthenticatedAs($target);
        $this->assertTrue(session()->has(ImpersonationService::SESSION_KEY));
        $this->assertSame($admin->id, session(ImpersonationService::SESSION_KEY));

        Livewire::test('impersonation-banner')
            ->call('leave')
            ->assertRedirect(route('panels.administrator.user-management.user.index'));

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse(session()->has(ImpersonationService::SESSION_KEY));
    }

    public function test_cannot_impersonate_self(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin);

        $this->expectException(ValidationException::class);

        app(ImpersonationService::class)->start($admin);
    }

    public function test_cannot_start_while_already_impersonating(): void
    {
        $admin = User::factory()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($admin);

        $service = app(ImpersonationService::class);
        $service->start($first);

        $this->expectException(ValidationException::class);

        $service->start($second);
    }
}
