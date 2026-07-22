<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Contact
        $this->migrator->add('contact.postal_code', null);
        $this->migrator->add('contact.fax', null);

        // Iranian social networks
        $this->migrator->add('social.eitaa', null);
        $this->migrator->add('social.bale', null);
        $this->migrator->add('social.rubika', null);
        $this->migrator->add('social.soroush', null);
        $this->migrator->add('social.aparat', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('contact.postal_code');
        $this->migrator->deleteIfExists('contact.fax');

        $this->migrator->deleteIfExists('social.eitaa');
        $this->migrator->deleteIfExists('social.bale');
        $this->migrator->deleteIfExists('social.rubika');
        $this->migrator->deleteIfExists('social.soroush');
        $this->migrator->deleteIfExists('social.aparat');
    }
};
