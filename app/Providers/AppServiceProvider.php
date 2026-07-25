<?php

namespace App\Providers;

use App\Models\Finance\Transaction;
use App\Models\Finance\Withdrawal;
use App\Observers\TransactionObserver;
use App\Observers\WithdrawalObserver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Morilog\Jalali\Jalalian;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Transaction::observe(TransactionObserver::class);
        Withdrawal::observe(WithdrawalObserver::class);

        Carbon::macro('toDynamicFormat', function (string $format = 'Y/m/d H:i:s'): string {
            /** @var Carbon $this */
            $timezone = Config::get('app.timezone', 'UTC');
            $date = $this->copy()->setTimezone($timezone);

            if (app()->getLocale() === 'fa') {
                return Jalalian::fromCarbon($date)->format($format);
            }

            return $date->format($format);
        });
    }
}
