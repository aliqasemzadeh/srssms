<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // General
        $this->migrator->add('general.site_name', config('app.name', 'SRS SMS'));
        $this->migrator->add('general.site_short_name', 'SRS');
        $this->migrator->add('general.site_description', '');
        $this->migrator->add('general.site_logo', null);
        $this->migrator->add('general.site_favicon', null);

        // Maintenance
        $this->migrator->add('maintenance.is_maintenance_mode', false);
        $this->migrator->add('maintenance.secret_token', null);
        $this->migrator->add('maintenance.message', null);

        // Security
        $this->migrator->add('security.is_registration_enabled', true);
        $this->migrator->add('security.banned_usernames', []);
        $this->migrator->add('security.banned_ips', []);

        // Contact
        $this->migrator->add('contact.address', null);
        $this->migrator->add('contact.phone_numbers', []);
        $this->migrator->add('contact.support_email', null);

        // Social
        $this->migrator->add('social.telegram', null);
        $this->migrator->add('social.instagram', null);
        $this->migrator->add('social.linkedin', null);
        $this->migrator->add('social.x_twitter', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('general.site_name');
        $this->migrator->deleteIfExists('general.site_short_name');
        $this->migrator->deleteIfExists('general.site_description');
        $this->migrator->deleteIfExists('general.site_logo');
        $this->migrator->deleteIfExists('general.site_favicon');

        $this->migrator->deleteIfExists('maintenance.is_maintenance_mode');
        $this->migrator->deleteIfExists('maintenance.secret_token');
        $this->migrator->deleteIfExists('maintenance.message');

        $this->migrator->deleteIfExists('security.is_registration_enabled');
        $this->migrator->deleteIfExists('security.banned_usernames');
        $this->migrator->deleteIfExists('security.banned_ips');

        $this->migrator->deleteIfExists('contact.address');
        $this->migrator->deleteIfExists('contact.phone_numbers');
        $this->migrator->deleteIfExists('contact.support_email');

        $this->migrator->deleteIfExists('social.telegram');
        $this->migrator->deleteIfExists('social.instagram');
        $this->migrator->deleteIfExists('social.linkedin');
        $this->migrator->deleteIfExists('social.x_twitter');
    }
};
