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
    ],
];
