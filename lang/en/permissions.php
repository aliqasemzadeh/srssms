<?php

/*
|--------------------------------------------------------------------------
| Permissions (grouped by role)
|--------------------------------------------------------------------------
| The complete list of application permissions. Permissions are grouped
| by role and follow the "group.action" naming convention, so the
| permission name "user-management.user.create" is translated by the key
| "permissions.administrator.user-management.user.create".
*/

return [
    'administrator' => [
        'user-management' => [
            'user' => [
                'view' => 'View Users',
                'create' => 'Create User',
                'edit' => 'Edit User',
                'delete' => 'Delete User',
                'import' => 'Import Users',
                'export' => 'Export Users',
            ],
            'role' => [
                'view' => 'View Roles',
                'create' => 'Create Role',
                'edit' => 'Edit Role',
                'delete' => 'Delete Role',
                'import' => 'Import Roles',
                'export' => 'Export Roles',
            ],
            'permission' => [
                'view' => 'View Permissions',
                'create' => 'Create Permission',
                'edit' => 'Edit Permission',
                'delete' => 'Delete Permission',
                'import' => 'Import Permissions',
                'export' => 'Export Permissions',
            ],
        ],
        'finance-management' => [
            'currency' => [
                'view' => 'View Currencies',
                'create' => 'Create Currency',
                'edit' => 'Edit Currency',
                'delete' => 'Delete Currency',
                'import' => 'Import Currencies',
                'export' => 'Export Currencies',
            ],
            'wallet' => [
                'view' => 'View Wallets',
                'create' => 'Create Wallet',
                'edit' => 'Edit Wallet',
                'delete' => 'Delete Wallet',
                'import' => 'Import Wallets',
                'export' => 'Export Wallets',
            ],
            'transaction' => [
                'view' => 'View Transactions',
                'create' => 'Create Transaction',
                'edit' => 'Edit Transaction',
                'delete' => 'Delete Transaction',
                'import' => 'Import Transactions',
                'export' => 'Export Transactions',
            ],
        ],
        'system-management' => [
            'setting' => [
                'view' => 'View Settings',
                'edit' => 'Edit Settings',
            ],
            'function' => [
                'view' => 'View Functions',
                'run' => 'Run Functions',
            ],
            'backup' => [
                'view' => 'View Backups',
                'create' => 'Create Backup',
                'download' => 'Download Backup',
                'delete' => 'Delete Backup',
            ],
            'log' => [
                'view' => 'View Logs',
            ],
        ],
    ],
];
