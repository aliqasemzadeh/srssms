<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('welcome_page.tariffs', [
            [
                'name' => 'ارسال پیامک تکی',
                'price' => '1650',
                'unit' => 'ریال',
                'description' => 'ارسال پیامک به ازای هر صفحه فارسی.',
            ],
            [
                'name' => 'ارسال پیامک انبوه',
                'price' => '1650',
                'unit' => 'ریال',
                'description' => 'ارسال به لیست‌های بزرگ با تخفیف ویژه.',
            ],
        ]);
    }

    public function down(): void
    {
        $this->migrator->delete('welcome_page.tariffs');
    }
};
