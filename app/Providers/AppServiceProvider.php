<?php

namespace App\Providers;

use App\Contracts\SmsSender;
use App\Models\Finance\Deposit;
use App\Models\Finance\Transaction;
use App\Models\Finance\Withdrawal;
use App\Observers\DepositObserver;
use App\Observers\TransactionObserver;
use App\Observers\WithdrawalObserver;
use App\Services\Sms\SmsSender as DomainSmsSender;
use App\Settings\PaymentSettings;
use App\Support\JalaliDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsSender::class, DomainSmsSender::class);
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

            return JalaliDate::format($date, $format) ?? $date->format($format);
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
