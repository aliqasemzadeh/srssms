<?php

namespace App\Support;

use App\Settings\PaymentSettings;
use Throwable;

class PaymentGateways
{
    /**
     * @return array<int, string>
     */
    public static function allDrivers(): array
    {
        return array_keys(config('payment.drivers', []));
    }

    public static function driverLabel(string $driver): string
    {
        $key = 'general.gateways.'.$driver;
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return str_replace('_', ' ', ucfirst($driver));
    }

    public static function methodLabel(string $method): string
    {
        if (str_starts_with($method, 'gateway_')) {
            return self::driverLabel(substr($method, 8));
        }

        $key = 'general.deposit_methods.'.$method;
        $translated = __($key);

        return $translated !== $key ? $translated : $method;
    }

    public static function isEndpointKey(string $key): bool
    {
        $callbackKeys = [
            'callbackUrl',
            'callback_url',
            'success_url',
            'cancel_url',
            'successReturnUrl',
            'failureReturnUrl',
        ];

        if (in_array($key, $callbackKeys, true)) {
            return false;
        }

        $lower = strtolower($key);

        if (str_contains($lower, 'url') || str_contains($lower, 'uri') || str_contains($lower, 'wsdl')) {
            return true;
        }

        return in_array($key, [
            'apiGetToken',
            'apiNormalSale',
            'apiConfirmationUrl',
            'apiDirectPaymentUrl',
            'apiGenerateTokenUrl',
            'apiTokenUrl',
            'apiPayStart',
            'apiPayVerify',
            'apiRestPaymentUrl',
            'atipayTokenUrl',
            'atipayRedirectGatewayUrl',
            'atipayVerifyUrl',
        ], true);
    }

    public static function isSecretKey(string $key): bool
    {
        $lower = strtolower($key);

        return str_contains($lower, 'password')
            || str_contains($lower, 'secret')
            || str_contains($lower, 'apikey')
            || preg_match('/(^|_)(key|pin|token)$/i', $key) === 1
            || in_array($key, ['key', 'signKey', 'pubKey', 'CorporationPin', 'apiSecret', 'client_secret'], true);
    }

    /**
     * @return array<int, string>
     */
    public static function editableKeys(string $driver): array
    {
        $config = config("payment.drivers.{$driver}", []);

        return collect($config)
            ->keys()
            ->reject(fn (string $key): bool => self::isEndpointKey($key))
            ->reject(fn (string $key): bool => is_array($config[$key] ?? null) || is_callable($config[$key] ?? null))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function enabledDrivers(): array
    {
        try {
            $enabled = app(PaymentSettings::class)->enabled;
        } catch (Throwable) {
            $enabled = ['zarinpal'];
        }

        $all = self::allDrivers();

        return array_values(array_filter(
            $enabled,
            fn (string $driver): bool => in_array($driver, $all, true)
        ));
    }

    /**
     * Deposit method options: enabled gateways + static non-gateway methods.
     *
     * @return array<string, string> method => translation key or resolved via methodLabel
     */
    public static function depositMethodOptions(): array
    {
        $options = [];

        foreach (self::enabledDrivers() as $driver) {
            $options['gateway_'.$driver] = 'general.gateways.'.$driver;
        }

        $static = config('finance.deposit_methods', []);

        foreach ($static as $method => $label) {
            if (str_starts_with($method, 'gateway_')) {
                continue;
            }

            $options[$method] = $label;
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function gatewayMethodOptions(): array
    {
        $options = [];

        foreach (self::enabledDrivers() as $driver) {
            $options['gateway_'.$driver] = self::driverLabel($driver);
        }

        // Also include any gateway_* methods that may exist historically
        foreach (array_keys(config('finance.deposit_methods', [])) as $method) {
            if (str_starts_with($method, 'gateway_') && ! isset($options[$method])) {
                $options[$method] = self::methodLabel($method);
            }
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function driverOptions(): array
    {
        return collect(self::allDrivers())
            ->mapWithKeys(fn (string $driver): array => [$driver => self::driverLabel($driver)])
            ->all();
    }
}
