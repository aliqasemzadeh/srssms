<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sms.default_driver', config('sms.default', 'log'));
        $this->migrator->add('sms.default_gateway_id', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('sms.default_driver');
        $this->migrator->deleteIfExists('sms.default_gateway_id');
    }
};
