<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Permission\PermissionCatalog;
use App\Services\Permission\SyncAdministratorPermissions;
use Illuminate\Console\Command;

class MakeUserAdminCommand extends Command
{
    protected $signature = 'user:make-admin
                            {user : User email or ID}';

    protected $description = 'Create the Administrator role with all permissions and assign it to a user';

    public function handle(SyncAdministratorPermissions $sync): int
    {
        $user = $this->resolveUser($this->argument('user'));

        if ($user === null) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $result = $sync->handle();

        $user->assignRole($result['role']);

        $this->info(sprintf(
            'User [%s] (#%d) is now an %s with %d permissions.',
            $user->email,
            $user->id,
            PermissionCatalog::ROLE_NAME,
            count($result['permissions']),
        ));

        return self::SUCCESS;
    }

    private function resolveUser(string $identifier): ?User
    {
        if (ctype_digit($identifier)) {
            return User::query()->find((int) $identifier);
        }

        return User::query()->where('email', $identifier)->first();
    }
}
