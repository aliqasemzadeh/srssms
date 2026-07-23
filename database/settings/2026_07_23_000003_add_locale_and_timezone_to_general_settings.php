<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.locale', 'fa');
        $this->migrator->add('general.timezone', 'Asia/Tehran');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('general.locale');
        $this->migrator->deleteIfExists('general.timezone');
    }
};
