<?php

namespace App\Services\Sms\Drivers;

use App\Contracts\Sms\SmsDriver;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Gateway;
use App\Services\Sms\SmsSendResult;
use App\Services\Sms\SmsStatusMapper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SabanovinDriver implements SmsDriver
{
    public function __construct(
        protected SmsStatusMapper $statusMapper,
    ) {}

    public function send(Gateway $gateway, array $mobiles, string $text): SmsSendResult
    {
        $apiKey = $this->apiKey($gateway);

        $url = sprintf(
            'https://api.sabanovin.com/v1/%s/sms/send.json',
            $apiKey
        );

        try {
            $response = Http::withoutVerifying()
                ->asForm()
                ->acceptJson()
                ->timeout(30)
                ->post($url, [
                    'gateway' => $gateway->number,
                    'to' => implode(',', $mobiles),
                    'text' => $text,
                ]);

            Log::info('SMS send attempt via Sabanovin', [
                'to' => implode(',', $mobiles),
                'message' => $text,
                'response' => $response->json(),
            ]);
        } catch (ConnectionException $e) {
            Log::error('Failed to send SMS: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->failedAll($mobiles, $e->getMessage());
        }

        $json = $response->json();

        if (! $response->successful()) {
            Log::error('Send SMS Error: '.($json['status']['message'] ?? 'unknown error'));

            return $this->failedAll($mobiles, $response->body(), $json);
        }

        $code = (int) data_get($json, 'status.code', $response->status());

        if ($code !== 200) {
            Log::error('Send SMS Error: '.(data_get($json, 'status.message', 'unknown error')));

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
                    'status' => $this->statusMapper->mapToValue(data_get($entry, 'status')),
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

        if (($batchId = data_get($json, 'batch_id')) !== null) {
            $json['batch_id'] = $batchId;
        }

        return new SmsSendResult(success: true, recipients: $recipients, raw: $json);
    }

    public function status(Gateway $gateway, ?string $batchId = null, array $referenceIds = []): array
    {
        $apiKey = $this->apiKey($gateway);
        $url = sprintf(
            'https://api.sabanovin.com/v1/%s/sms/status.json',
            $apiKey
        );

        $entries = [];
        $rawResponses = [];

        if (filled($batchId)) {
            $result = $this->requestStatus($url, ['batch_id' => $batchId]);
            $rawResponses[] = $result['raw'];
            $entries = array_merge($entries, $result['entries']);
        } else {
            foreach (array_unique(array_filter($referenceIds)) as $referenceId) {
                $result = $this->requestStatus($url, ['reference_id' => $referenceId]);
                $rawResponses[] = $result['raw'];
                $entries = array_merge($entries, $result['entries']);
            }
        }

        return [
            'entries' => $entries,
            'raw' => count($rawResponses) === 1 ? $rawResponses[0] : $rawResponses,
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{entries: array<int, array{reference_id: ?string, mobile: ?string, status: string, datetime: ?string}>, raw: mixed}
     */
    protected function requestStatus(string $url, array $params): array
    {
        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->timeout(30)
                ->asForm()
                ->post($url, $params);
        } catch (ConnectionException $e) {
            Log::error('Failed to fetch SMS status: '.$e->getMessage());

            return ['entries' => [], 'raw' => ['error' => $e->getMessage()]];
        }

        $json = $response->json();
        $code = (int) data_get($json, 'status.code', $response->status());

        if (! $response->successful() || $code !== 200) {
            Log::error('SMS status Error: '.(data_get($json, 'status.message', 'unknown error')));

            return ['entries' => [], 'raw' => $json];
        }

        $entries = [];

        foreach (data_get($json, 'entries', []) ?: [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $entries[] = [
                'reference_id' => data_get($entry, 'reference_id') !== null
                    ? (string) data_get($entry, 'reference_id')
                    : null,
                'mobile' => data_get($entry, 'number') !== null
                    ? (string) data_get($entry, 'number')
                    : (data_get($entry, 'mobile') !== null ? (string) data_get($entry, 'mobile') : null),
                'status' => $this->statusMapper->mapToValue(data_get($entry, 'status')),
                'datetime' => data_get($entry, 'datetime') !== null
                    ? (string) data_get($entry, 'datetime')
                    : null,
            ];
        }

        return ['entries' => $entries, 'raw' => $json];
    }

    protected function apiKey(Gateway $gateway): string
    {
        $apiKey = (string) $gateway->provider?->credential('api_key', '');

        if ($apiKey === '') {
            throw new RuntimeException('Sabanovin API key is missing on the provider.');
        }

        return $apiKey;
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
}
