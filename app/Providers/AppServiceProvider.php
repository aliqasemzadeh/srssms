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
            $settings = app(PaymentSettings::class);
        } catch (Throwable) {
            return;
        }

        if (filled($settings->default)) {
            Config::set('payment.default', $settings->default);
        }

        foreach ($settings->drivers as $name => $overrides) {
            if (! is_string($name) || ! is_array($overrides) || $overrides === []) {
                continue;
            }

            $current = Config::get("payment.drivers.{$name}", []);

            if (! is_array($current)) {
                continue;
            }

            $casted = [];

            foreach ($overrides as $key => $value) {
                if (! is_string($key)) {
                    continue;
                }

                $base = $current[$key] ?? null;

                if (is_bool($base)) {
                    $casted[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } elseif (is_int($base)) {
                    $casted[$key] = (int) $value;
                } elseif (is_float($base)) {
                    $casted[$key] = (float) $value;
                } else {
                    $casted[$key] = $value;
                }
            }

            Config::set("payment.drivers.{$name}", array_replace_recursive($current, $casted));
        }
    }
}
