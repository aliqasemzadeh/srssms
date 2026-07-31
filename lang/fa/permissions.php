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
                'impersonate' => 'ورود به‌جای کاربر',
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
            'transaction' => [
                'view' => 'مشاهده تراکنش‌ها',
                'create' => 'ایجاد تراکنش',
                'edit' => 'ویرایش تراکنش',
                'delete' => 'حذف تراکنش',
                'import' => 'وارد کردن تراکنش‌ها',
                'export' => 'خروجی گرفتن از تراکنش‌ها',
            ],
            'deposit' => [
                'view' => 'مشاهده واریزها',
                'create' => 'ایجاد واریز',
                'edit' => 'ویرایش واریز',
                'delete' => 'حذف واریز',
                'import' => 'وارد کردن واریزها',
                'export' => 'خروجی گرفتن از واریزها',
            ],
            'withdrawal' => [
                'view' => 'مشاهده برداشت‌ها',
                'create' => 'ایجاد برداشت',
                'edit' => 'ویرایش برداشت',
                'delete' => 'حذف برداشت',
                'import' => 'وارد کردن برداشت‌ها',
                'export' => 'خروجی گرفتن از برداشت‌ها',
            ],
            'payment' => [
                'view' => 'مشاهده پرداخت‌های درگاهی',
                'check' => 'تایید یا رد پرداخت درگاهی',
                'setting' => [
                    'view' => 'مشاهده تنظیمات درگاه پرداخت',
                    'edit' => 'ویرایش تنظیمات درگاه پرداخت',
                ],
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
        'support-system' => [
            'ticket' => [
                'view' => 'مشاهده تیکت‌ها',
                'reply' => 'پاسخ به تیکت‌ها',
                'update' => 'ویرایش وضعیت تیکت‌ها',
                'delete' => 'حذف تیکت‌ها',
            ],
        ],
        'content-management' => [
            'article' => [
                'view' => 'مشاهده مقالات',
                'create' => 'ایجاد مقاله',
                'edit' => 'ویرایش مقاله',
                'delete' => 'حذف مقاله',
            ],
        ],
        'sms-management' => [
            'provider' => [
                'view' => 'مشاهده پروایدرها',
                'create' => 'ایجاد پروایدر',
                'edit' => 'ویرایش پروایدر',
                'delete' => 'حذف پروایدر',
                'detail' => 'مشاهده جزئیات پروایدر',
            ],
            'gateway' => [
                'view' => 'مشاهده درگاه‌های پیامک',
                'create' => 'ایجاد درگاه پیامک',
                'edit' => 'ویرایش درگاه پیامک',
                'delete' => 'حذف درگاه پیامک',
                'user' => [
                    'view' => 'مشاهده کاربران درگاه',
                    'create' => 'اعطای دسترسی درگاه',
                    'delete' => 'حذف دسترسی درگاه',
                ],
            ],
            'message' => [
                'view' => 'مشاهده پیامک‌ها',
            ],
            'setting' => [
                'view' => 'مشاهده تنظیمات پیامک',
                'edit' => 'ویرایش تنظیمات پیامک',
            ],
        ],
    ],
];
