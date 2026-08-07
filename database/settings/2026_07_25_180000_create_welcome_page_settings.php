<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('welcome_page.hero_subtitle', 'سامانه پیامک حرفه‌ای برای کسب‌وکارها و توسعه‌دهندگان');

        $this->migrator->add('welcome_page.typewriter_phrases', [
            'ارسال سریع و پایدار پیامک',
            'مدیریت دفترچه تلفن هوشمند',
            'API قدرتمند برای یکپارچه‌سازی',
            'کیف پول و گزارش‌گیری شفاف',
        ]);

        $this->migrator->add('welcome_page.typewriter_type_delay', 80);
        $this->migrator->add('welcome_page.typewriter_delete_delay', 40);
        $this->migrator->add('welcome_page.typewriter_pause_delay', 2000);

        $this->migrator->add('welcome_page.features', [
            [
                'title' => 'ارسال سریع پیامک',
                'description' => 'ارسال تکی و انبوه با سرعت بالا و پیگیری وضعیت تحویل.',
                'icon' => 'message-square',
            ],
            [
                'title' => 'دفترچه تلفن',
                'description' => 'مدیریت مخاطبین، گروه‌ها و برچسب‌ها برای هدف‌گیری بهتر.',
                'icon' => 'book-user',
            ],
            [
                'title' => 'API توسعه‌دهندگان',
                'description' => 'اتصال آسان به سامانه از طریق توکن و مستندات آماده.',
                'icon' => 'code',
            ],
            [
                'title' => 'کیف پول',
                'description' => 'شارژ، تراکنش‌ها و مدیریت هزینه ارسال در یک نگاه.',
                'icon' => 'wallet',
            ],
        ]);

        $this->migrator->add('welcome_page.tariffs', [
            [
                'name' => 'ارسال پیامک تکی',
                'price' => '650',
                'unit' => 'ریال',
                'description' => 'ارسال پیامک به ازای هر صفحه فارسی.',
            ],
            [
                'name' => 'ارسال پیامک انبوه',
                'price' => '600',
                'unit' => 'ریال',
                'description' => 'ارسال به لیست‌های بزرگ با تخفیف ویژه.',
            ],
        ]);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('welcome_page.hero_subtitle');
        $this->migrator->deleteIfExists('welcome_page.typewriter_phrases');
        $this->migrator->deleteIfExists('welcome_page.typewriter_type_delay');
        $this->migrator->deleteIfExists('welcome_page.typewriter_delete_delay');
        $this->migrator->deleteIfExists('welcome_page.typewriter_pause_delay');
        $this->migrator->deleteIfExists('welcome_page.features');
        $this->migrator->deleteIfExists('welcome_page.tariffs');
    }
};
