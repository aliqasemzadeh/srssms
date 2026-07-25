<?php

namespace App\Services\Sms\Drivers;

use App\Contracts\Sms\SmsDriver;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Gateway;
use App\Services\Sms\SmsSendResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SabanovinDriver implements SmsDriver
{
    public function send(Gateway $gateway, array $mobiles, string $text): SmsSendResult
    {
        $provider = $gateway->provider;
        $apiKey = (string) $provider?->credential('api_key', '');

        if ($apiKey === '') {
            throw new RuntimeException('Sabanovin API key is missing on the provider.');
        }

        // Do not url-encode the API key: Sabanovin keys contain ":" (e.g. sa123:token)
        // and encoding it to %3A makes the provider return 401 invalid API key.
        $url = sprintf(
            'https://api.sabanovin.com/v1/%s/sms/send.json',
            $apiKey
        );

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(30)
                ->post($url, [
                    'gateway' => $gateway->number,
                    'to' => implode(',', $mobiles),
                    'text' => $text,
                ]);
        } catch (ConnectionException $e) {
            return $this->failedAll($mobiles, $e->getMessage());
        }

        $json = $response->json();

        if (! $response->successful()) {
            return $this->failedAll($mobiles, $response->body(), $json);
        }

        $code = (int) data_get($json, 'status.code', $response->status());

        if ($code !== 200) {
            return $this->failedAll(
                $mobiles,
                (string) data_get($json, 'status.message', 'Sabanovin send failed'),
                $json
            );
        }

        $entries = data_get($json, 'entries', []);
        $recipients = [];

        if (is_array($entries) && $entries !== []) {
            foreach ($entries as $entry) {
                $recipients[] = [
                    'mobile' => (string) data_get($entry, 'mobile', data_get($entry, 'number', '')),
                    'status' => $this->mapStatus(data_get($entry, 'status')),
                    'reference_id' => data_get($entry, 'reference_id') !== null
                        ? (string) data_get($entry, 'reference_id')
                        : null,
                    'error' => null,
                ];
            }
        } else {
            foreach ($mobiles as $mobile) {
                $recipients[] = [
                    'mobile' => $mobile,
                    'status' => SmsMessageStatusEnum::Sent->value,
                    'reference_id' => null,
                    'error' => null,
                ];
            }
        }

        return new SmsSendResult(success: true, recipients: $recipients, raw: $json);
    }

    /**
     * @param  array<int, string>  $mobiles
     */
    protected function failedAll(array $mobiles, string $error, mixed $raw = null): SmsSendResult
    {
        $recipients = array_map(
            fn (string $mobile): array => [
                'mobile' => $mobile,
                'status' => SmsMessageStatusEnum::Failed->value,
                'reference_id' => null,
                'error' => $error,
            ],
            $mobiles
        );

        return new SmsSendResult(success: false, recipients: $recipients, message: $error, raw: $raw);
    }

    protected function mapStatus(mixed $status): string
    {
        $value = is_numeric($status) ? (int) $status : strtolower((string) $status);

        return match ($value) {
            1, '1', 'sent', 'success' => SmsMessageStatusEnum::Sent->value,
            2, '2', 'delivered' => SmsMessageStatusEnum::Delivered->value,
            3, '3', 'failed', 'error' => SmsMessageStatusEnum::Failed->value,
            default => SmsMessageStatusEnum::Sent->value,
        };
    }
}
