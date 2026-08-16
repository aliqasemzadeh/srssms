<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $tariffs = [
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
        ];

        try {
            $this->migrator->add('welcome_page.tariffs', $tariffs);
        } catch (\Throwable) {
            $this->migrator->update('welcome_page.tariffs', fn () => $tariffs);
        }
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('welcome_page.tariffs');
    }
};
