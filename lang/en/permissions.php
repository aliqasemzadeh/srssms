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
                'impersonate' => 'Impersonate User',
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
            'deposit' => [
                'view' => 'View Deposits',
                'create' => 'Create Deposit',
                'edit' => 'Edit Deposit',
                'delete' => 'Delete Deposit',
                'import' => 'Import Deposits',
                'export' => 'Export Deposits',
            ],
            'withdrawal' => [
                'view' => 'View Withdrawals',
                'create' => 'Create Withdrawal',
                'edit' => 'Edit Withdrawal',
                'delete' => 'Delete Withdrawal',
                'import' => 'Import Withdrawals',
                'export' => 'Export Withdrawals',
            ],
            'payment' => [
                'view' => 'View Gateway Payments',
                'check' => 'Approve or Reject Gateway Payment',
                'setting' => [
                    'view' => 'View Payment Gateway Settings',
                    'edit' => 'Edit Payment Gateway Settings',
                ],
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
        'support-system' => [
            'ticket' => [
                'view' => 'View Tickets',
                'reply' => 'Reply to Tickets',
                'update' => 'Update Ticket Status',
                'delete' => 'Delete Tickets',
            ],
        ],
        'content-management' => [
            'article' => [
                'view' => 'View Articles',
                'create' => 'Create Article',
                'edit' => 'Edit Article',
                'delete' => 'Delete Article',
            ],
        ],
        'sms-management' => [
            'provider' => [
                'view' => 'View Providers',
                'create' => 'Create Provider',
                'edit' => 'Edit Provider',
                'delete' => 'Delete Provider',
                'detail' => 'View Provider Details',
            ],
            'gateway' => [
                'view' => 'View SMS Gateways',
                'create' => 'Create SMS Gateway',
                'edit' => 'Edit SMS Gateway',
                'delete' => 'Delete SMS Gateway',
                'user' => [
                    'view' => 'View Gateway Users',
                    'create' => 'Grant Gateway Access',
                    'delete' => 'Revoke Gateway Access',
                ],
            ],
            'message' => [
                'view' => 'View SMS Messages',
            ],
            'setting' => [
                'view' => 'View SMS Settings',
                'edit' => 'Edit SMS Settings',
            ],
        ],
    ],
];
