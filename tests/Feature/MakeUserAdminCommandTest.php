<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Permission\PermissionCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MakeUserAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_administrator_role_with_all_permissions_and_assigns_user(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('user:make-admin', ['user' => 'admin@example.com'])
            ->assertSuccessful();

        $role = Role::findByName(PermissionCatalog::ROLE_NAME);

        $this->assertTrue($user->fresh()->hasRole(PermissionCatalog::ROLE_NAME));
        $this->assertGreaterThan(0, $role->permissions()->count());
        $this->assertSame(
            count(app(PermissionCatalog::class)->names()),
            $role->permissions()->count(),
        );
    }

    public function test_it_accepts_user_id(): void
    {
        $user = User::factory()->create();

        $this->artisan('user:make-admin', ['user' => (string) $user->id])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->hasRole(PermissionCatalog::ROLE_NAME));
    }

    public function test_it_fails_when_user_is_missing(): void
    {
        $this->artisan('user:make-admin', ['user' => 'missing@example.com'])
            ->assertFailed();
    }

    public function test_guest_cannot_access_administrator_panel(): void
    {
        $this->get(route('panels.administrator.dashboard.index'))
            ->assertRedirect();
    }

    public function test_user_without_role_cannot_access_administrator_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('panels.administrator.dashboard.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_access_dashboard(): void
    {
        $user = $this->makeAdministrator(User::factory()->create());

        $this->actingAs($user)
            ->get(route('panels.administrator.dashboard.index'))
            ->assertOk();
    }
}
