<?php

namespace App\Livewire\Concerns;

trait AuthorizesAdministratorPermissions
{
    protected function authorizePermission(string $permission): void
    {
        abort_unless(
            auth()->user()?->can($permission) === true,
            403,
        );
    }
}
