<?php

namespace Tests;

use App\Models\User;
use App\Services\Permission\PermissionCatalog;
use App\Services\Permission\SyncAdministratorPermissions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function makeAdministrator(User $user): User
    {
        app(SyncAdministratorPermissions::class)->handle();

        $user->assignRole(PermissionCatalog::ROLE_NAME);

        return $user->fresh();
    }
}
