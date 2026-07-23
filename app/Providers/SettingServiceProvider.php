<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * Applies the stored settings to the framework configuration on every
     * boot. Settings are read through the spatie settings cache, so no extra
     * database query is executed once the cache is warm. Wrapped in rescue()
     * so a missing settings table (fresh install, pending migrations) never
     * breaks the application boot.
     */
    public function boot(): void
    {
        rescue(function (): void {
            $general = app(GeneralSettings::class);

            config([
                'app.name' => $general->site_name,
                'app.locale' => $general->locale,
                'app.timezone' => $general->timezone,
            ]);

            app()->setLocale($general->locale);
            date_default_timezone_set($general->timezone);

            View::share('generalSettings', $general);
        }, report: false);
    }
}
