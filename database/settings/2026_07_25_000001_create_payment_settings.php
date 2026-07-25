<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payment.default', config('payment.default', 'zarinpal'));
        $this->migrator->add('payment.enabled', ['zarinpal']);
        $this->migrator->add('payment.drivers', []);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('payment.default');
        $this->migrator->deleteIfExists('payment.enabled');
        $this->migrator->deleteIfExists('payment.drivers');
    }
};
