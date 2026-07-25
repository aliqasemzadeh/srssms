<?php

namespace App\Settings;

use Illuminate\Support\Facades\Config;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

class PaymentSettings extends Settings
{
    public string $default;

    /** @var array<int, string> */
    public array $enabled;

    public array $drivers;

    public static function group(): string
    {
        return 'payment';
    }

    /**
     * Clear settings cache, write this instance back, and merge into payment config.
     */
    public function refreshCache(): self
    {
        $cache = app(SettingsCacheFactory::class)->build(static::repository());

        if ($cache->isEnabled()) {
            $cache->clear();
            $cache->put($this);
        }

        $this->applyToConfig();

        return $this;
    }

    /**
     * Merge stored overrides into runtime payment config.
     */
    public function applyToConfig(): void
    {
        if (filled($this->default)) {
            Config::set('payment.default', $this->default);
        }

        foreach ($this->drivers as $name => $overrides) {
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
