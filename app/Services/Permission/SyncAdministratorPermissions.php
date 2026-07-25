<?php

namespace App\Services\Permission;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncAdministratorPermissions
{
    public function __construct(
        private readonly PermissionCatalog $catalog,
    ) {}

    /**
     * Ensure Administrator role exists, sync all catalog permissions, and attach them to the role.
     *
     * @return array{role: Role, permissions: list<string>}
     */
    public function handle(string $guard = 'web'): array
    {
        $this->catalog->forget();

        $names = $this->catalog->names();

        $permissions = collect($names)->map(
            fn (string $name): Permission => Permission::findOrCreate($name, $guard),
        );

        $role = Role::findOrCreate(PermissionCatalog::ROLE_NAME, $guard);
        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->catalog->forget();

        return [
            'role' => $role->fresh(),
            'permissions' => $names,
        ];
    }
}
