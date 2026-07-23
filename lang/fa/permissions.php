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
                'view' => 'مشاهده کاربران',
                'create' => 'ایجاد کاربر',
                'edit' => 'ویرایش کاربر',
                'delete' => 'حذف کاربر',
                'import' => 'وارد کردن کاربران',
                'export' => 'خروجی گرفتن از کاربران',
            ],
            'role' => [
                'view' => 'مشاهده نقش‌ها',
                'create' => 'ایجاد نقش',
                'edit' => 'ویرایش نقش',
                'delete' => 'حذف نقش',
                'import' => 'وارد کردن نقش‌ها',
                'export' => 'خروجی گرفتن از نقش‌ها',
            ],
            'permission' => [
                'view' => 'مشاهده مجوزها',
                'create' => 'ایجاد مجوز',
                'edit' => 'ویرایش مجوز',
                'delete' => 'حذف مجوز',
                'import' => 'وارد کردن مجوزها',
                'export' => 'خروجی گرفتن از مجوزها',
            ],
        ],
        'finance-management' => [
            'currency' => [
                'view' => 'مشاهده ارزها',
                'create' => 'ایجاد ارز',
                'edit' => 'ویرایش ارز',
                'delete' => 'حذف ارز',
                'import' => 'وارد کردن ارزها',
                'export' => 'خروجی گرفتن از ارزها',
            ],
            'wallet' => [
                'view' => 'مشاهده کیف پول‌ها',
                'create' => 'ایجاد کیف پول',
                'edit' => 'ویرایش کیف پول',
                'delete' => 'حذف کیف پول',
                'import' => 'وارد کردن کیف پول‌ها',
                'export' => 'خروجی گرفتن از کیف پول‌ها',
            ],
        ],
        'system-management' => [
            'setting' => [
                'view' => 'مشاهده تنظیمات',
                'edit' => 'ویرایش تنظیمات',
            ],
            'function' => [
                'view' => 'مشاهده عملکردها',
                'run' => 'اجرای عملکردها',
            ],
            'backup' => [
                'view' => 'مشاهده پشتیبان‌ها',
                'create' => 'ایجاد پشتیبان',
                'download' => 'دانلود پشتیبان',
                'delete' => 'حذف پشتیبان',
            ],
            'log' => [
                'view' => 'مشاهده لاگ‌ها',
            ],
        ],
    ],
];
