<?php

namespace Tests\Feature;

use App\Livewire\Concerns\InteractsWithPermissionLabels;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_role_and_permission_index_pages_render(): void
    {
        $this->get(route('panels.administrator.user-management.role.index'))->assertOk();
        $this->get(route('panels.administrator.user-management.permission.index'))->assertOk();
    }

    public function test_role_can_be_created_updated_and_deleted(): void
    {
        Livewire::test('user-management.role.create')
            ->set('form.name', 'manager')
            ->call('save');

        $this->assertDatabaseHas('roles', ['name' => 'manager', 'guard_name' => 'web']);

        $role = Role::findByName('manager');

        Livewire::test('user-management.role.edit')
            ->dispatch('panels.administrator.user-management.role.edit.assign-data', role: $role->id)
            ->set('form.name', 'supervisor')
            ->call('save');

        $this->assertDatabaseHas('roles', ['name' => 'supervisor']);

        Livewire::test('user-management.role.delete')
            ->dispatch('panels.administrator.user-management.role.delete.assign-data', role: $role->id)
            ->call('delete');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_permission_builder_creates_grouped_permissions(): void
    {
        Livewire::test('user-management.permission.create')
            ->set('form.mode', 'builder')
            ->set('form.group', 'user-management.user')
            ->set('form.actions', ['view', 'create'])
            ->call('save');

        $this->assertDatabaseHas('permissions', ['name' => 'user-management.user.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'user-management.user.create']);
    }

    public function test_permission_manual_mode_and_edit_and_delete(): void
    {
        Livewire::test('user-management.permission.create')
            ->set('form.mode', 'manual')
            ->set('form.name', 'reports.generate')
            ->call('save');

        $permission = Permission::findByName('reports.generate');

        Livewire::test('user-management.permission.edit')
            ->dispatch('panels.administrator.user-management.permission.edit.assign-data', permission: $permission->id)
            ->set('form.name', 'reports.export')
            ->call('save');

        $this->assertDatabaseHas('permissions', ['name' => 'reports.export']);

        Livewire::test('user-management.permission.delete')
            ->dispatch('panels.administrator.user-management.permission.delete.assign-data', permission: $permission->id)
            ->call('delete');

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    public function test_role_permissions_can_be_granted_and_revoked(): void
    {
        $role = Role::create(['name' => 'manager']);
        $view = Permission::create(['name' => 'user-management.user.view']);
        $edit = Permission::create(['name' => 'user-management.user.edit']);
        $role->givePermissionTo($edit);

        Livewire::test('user-management.role.permissions')
            ->dispatch('panels.administrator.user-management.role.permissions.assign-data', role: $role->id)
            ->call('confirm', 'grant', $view->id)
            ->call('apply')
            ->call('confirm', 'revoke', $edit->id)
            ->call('apply');

        $role->refresh();

        $this->assertTrue($role->hasPermissionTo('user-management.user.view'));
        $this->assertFalse($role->hasPermissionTo('user-management.user.edit'));
    }

    public function test_role_permissions_grant_all_and_revoke_all(): void
    {
        $role = Role::create(['name' => 'manager']);
        Permission::create(['name' => 'user-management.user.view']);
        Permission::create(['name' => 'user-management.user.edit']);

        $component = Livewire::test('user-management.role.permissions')
            ->dispatch('panels.administrator.user-management.role.permissions.assign-data', role: $role->id)
            ->call('confirm', 'grant-all')
            ->call('apply');

        $this->assertCount(2, $role->fresh()->permissions);

        $component
            ->call('confirm', 'revoke-all')
            ->call('apply');

        $this->assertCount(0, $role->fresh()->permissions);
    }

    public function test_role_users_can_be_granted_and_revoked(): void
    {
        $role = Role::create(['name' => 'manager']);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userB->assignRole($role);

        Livewire::test('user-management.role.users')
            ->dispatch('panels.administrator.user-management.role.users.assign-data', role: $role->id)
            ->call('confirm', 'grant', $userA->id)
            ->call('apply')
            ->call('confirm', 'revoke', $userB->id)
            ->call('apply');

        $this->assertTrue($userA->fresh()->hasRole('manager'));
        $this->assertFalse($userB->fresh()->hasRole('manager'));
    }

    public function test_permission_roles_and_users_transfer(): void
    {
        $permission = Permission::create(['name' => 'reports.view']);
        $role = Role::create(['name' => 'manager']);
        $user = User::factory()->create();

        Livewire::test('user-management.permission.roles')
            ->dispatch('panels.administrator.user-management.permission.roles.assign-data', permission: $permission->id)
            ->call('confirm', 'grant', $role->id)
            ->call('apply');

        $this->assertTrue($role->fresh()->hasPermissionTo('reports.view'));

        Livewire::test('user-management.permission.users')
            ->dispatch('panels.administrator.user-management.permission.users.assign-data', permission: $permission->id)
            ->call('confirm', 'grant', $user->id)
            ->call('apply');

        $this->assertTrue($user->fresh()->hasDirectPermission('reports.view'));
    }

    public function test_user_roles_and_permissions_transfer(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['name' => 'reports.view']);

        Livewire::test('user-management.user.roles')
            ->dispatch('panels.administrator.user-management.user.roles.assign-data', user: $user->id)
            ->call('confirm', 'grant', $role->id)
            ->call('apply');

        $this->assertTrue($user->fresh()->hasRole('manager'));

        Livewire::test('user-management.user.permissions')
            ->dispatch('panels.administrator.user-management.user.permissions.assign-data', user: $user->id)
            ->call('confirm', 'grant', $permission->id)
            ->call('apply');

        $this->assertTrue($user->fresh()->hasDirectPermission('reports.view'));

        Livewire::test('user-management.user.permissions')
            ->dispatch('panels.administrator.user-management.user.permissions.assign-data', user: $user->id)
            ->call('confirm', 'revoke', $permission->id)
            ->call('apply');

        $this->assertFalse($user->fresh()->hasDirectPermission('reports.view'));
    }

    public function test_permission_labels_are_translated_from_lang_file(): void
    {
        app()->setLocale('fa');

        $component = new class
        {
            use InteractsWithPermissionLabels;
        };

        $this->assertSame('مشاهده کاربران', $component->permissionLabel('user-management.user.view'));
        $this->assertSame('unknown.permission', $component->permissionLabel('unknown.permission'));
    }
}
