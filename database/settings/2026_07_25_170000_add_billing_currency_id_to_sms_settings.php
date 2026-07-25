<?php

use App\Models\Finance\Currency;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaultCurrencyId = Currency::query()
            ->where('is_active', true)
            ->where('type', 'fiat')
            ->orderBy('id')
            ->value('id');

        $this->migrator->add('sms.billing_currency_id', $defaultCurrencyId);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('sms.billing_currency_id');
    }
};
