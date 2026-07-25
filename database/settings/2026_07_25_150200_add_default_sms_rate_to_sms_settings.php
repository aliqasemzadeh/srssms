<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sms.default_sms_rate', '1500');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('sms.default_sms_rate');
    }
};
