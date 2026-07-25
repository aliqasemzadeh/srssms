<?php

namespace App\Services\Sms;

use App\Contracts\Sms\SmsDriver;
use App\Models\Sms\Gateway;
use App\Models\Sms\Provider;
use App\Services\Sms\Drivers\LogDriver;
use App\Services\Sms\Drivers\SabanovinDriver;
use InvalidArgumentException;

class SmsManager
{
    /**
     * @var array<string, class-string<SmsDriver>>
     */
    protected array $drivers = [
        'log' => LogDriver::class,
        'sabanovin' => SabanovinDriver::class,
    ];

    public function driverFor(Provider|Gateway|string $source): SmsDriver
    {
        $driver = match (true) {
            $source instanceof Gateway => $source->provider?->driver,
            $source instanceof Provider => $source->driver,
            default => $source,
        };

        $driver = $driver ?: (string) config('sms.default', 'log');

        return $this->driver($driver);
    }

    public function driver(string $name): SmsDriver
    {
        $class = $this->drivers[$name] ?? config("sms.drivers.{$name}.class");

        if (! is_string($class) || ! class_exists($class)) {
            throw new InvalidArgumentException("SMS driver [{$name}] is not supported.");
        }

        return app($class);
    }

    /**
     * @return array<string, string>
     */
    public function driverOptions(): array
    {
        $options = [];

        foreach (array_keys(config('sms.drivers', $this->drivers)) as $driver) {
            $key = 'general.sms_drivers.'.$driver;
            $translated = __($key);
            $options[$driver] = $translated !== $key
                ? $translated
                : str_replace('_', ' ', ucfirst($driver));
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public function editableCredentialKeys(string $driver): array
    {
        $fields = config("sms.drivers.{$driver}.credentials", []);

        return is_array($fields) ? array_keys($fields) : [];
    }
}
