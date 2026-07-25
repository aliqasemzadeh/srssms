<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::findOrCreate('user-management.user.impersonate', 'web');

        $related = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'user-management.user.edit',
                'user-management.user.view',
            ])
            ->pluck('id');

        if ($related->isEmpty()) {
            return;
        }

        Role::query()
            ->where('guard_name', 'web')
            ->whereHas('permissions', fn ($query) => $query->whereIn('permissions.id', $related))
            ->each(fn (Role $role) => $role->givePermissionTo($permission));
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::query()
            ->where('name', 'user-management.user.impersonate')
            ->where('guard_name', 'web')
            ->delete();
    }
};
