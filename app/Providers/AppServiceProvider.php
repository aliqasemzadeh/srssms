<?php

namespace App\Providers;

use App\Models\Finance\Deposit;
use App\Models\Finance\Transaction;
use App\Models\Finance\Withdrawal;
use App\Observers\DepositObserver;
use App\Observers\TransactionObserver;
use App\Observers\WithdrawalObserver;
use App\Settings\PaymentSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Morilog\Jalali\Jalalian;
use Throwable;

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
        Deposit::observe(DepositObserver::class);
        Withdrawal::observe(WithdrawalObserver::class);

        $this->mergePaymentSettings();

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

    protected function mergePaymentSettings(): void
    {
        try {
            app(PaymentSettings::class)->applyToConfig();
        } catch (Throwable) {
            return;
        }
    }
}
