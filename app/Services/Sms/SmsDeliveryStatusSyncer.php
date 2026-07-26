<?php

namespace App\Services\Sms;

use App\Enums\Sms\SmsDirectionEnum;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Message;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsDeliveryStatusSyncer
{
    public function __construct(
        protected SmsManager $manager,
        protected SmsStatusMapper $statusMapper,
    ) {}

    /**
     * @return array{updated: int, skipped: bool, reason: ?string}
     */
    public function sync(Message $message): array
    {
        $message->loadMissing(['gateway.provider', 'recipients']);

        if ($message->direction !== SmsDirectionEnum::Outbound) {
            return ['updated' => 0, 'skipped' => true, 'reason' => 'not_outbound'];
        }

        $gateway = $message->gateway;

        if (! $gateway || ! $gateway->provider) {
            return ['updated' => 0, 'skipped' => true, 'reason' => 'no_gateway'];
        }

        $batchId = data_get($message->provider_payload, 'batch_id');
        $batchId = $batchId !== null && $batchId !== '' ? (string) $batchId : null;

        $referenceIds = $message->recipients
            ->pluck('reference_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if ($batchId === null && $referenceIds === []) {
            return ['updated' => 0, 'skipped' => true, 'reason' => 'no_ids'];
        }

        try {
            $result = $this->manager->driverFor($gateway)->status($gateway, $batchId, $referenceIds);
        } catch (Throwable $e) {
            Log::error('SMS delivery status sync failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            return ['updated' => 0, 'skipped' => true, 'reason' => 'driver_error'];
        }

        $updated = 0;

        foreach ($result['entries'] as $entry) {
            $status = SmsMessageStatusEnum::tryFrom($entry['status'])
                ?? $this->statusMapper->map($entry['status']);

            $recipient = null;

            if (! empty($entry['reference_id'])) {
                $recipient = $message->recipients->first(
                    fn ($row) => (string) $row->reference_id === (string) $entry['reference_id']
                );
            }

            if (! $recipient && ! empty($entry['mobile'])) {
                $mobile = $this->normalizeMobile((string) $entry['mobile']);
                $recipient = $message->recipients->first(
                    fn ($row) => $this->normalizeMobile((string) $row->mobile) === $mobile
                );
            }

            if (! $recipient) {
                continue;
            }

            $payload = [
                'status' => $status,
            ];

            if ($status === SmsMessageStatusEnum::Delivered && ! $recipient->delivered_at) {
                $payload['delivered_at'] = now();
            }

            $recipient->update($payload);
            $updated++;
        }

        $message->provider_payload = array_merge($message->provider_payload ?? [], [
            'status_poll' => [
                'at' => now()->toDateTimeString(),
                'raw' => $result['raw'],
            ],
            'batch_id' => $batchId ?? data_get($message->provider_payload, 'batch_id'),
        ]);
        $message->save();

        $this->syncMessageStatus($message->fresh(['recipients']));

        return ['updated' => $updated, 'skipped' => false, 'reason' => null];
    }

    public function syncMessageStatus(?Message $message): void
    {
        if (! $message) {
            return;
        }

        $statuses = $message->recipients()->pluck('status')->map(
            fn ($status) => $status instanceof SmsMessageStatusEnum
                ? $status
                : SmsMessageStatusEnum::tryFrom((string) $status)
        )->filter();

        if ($statuses->isEmpty()) {
            return;
        }

        if ($statuses->every(fn (SmsMessageStatusEnum $status) => $status === SmsMessageStatusEnum::Delivered)) {
            $message->update(['status' => SmsMessageStatusEnum::Delivered]);

            return;
        }

        if ($statuses->every(fn (SmsMessageStatusEnum $status) => $status === SmsMessageStatusEnum::Failed)) {
            $message->update(['status' => SmsMessageStatusEnum::Failed]);

            return;
        }

        if ($statuses->contains(SmsMessageStatusEnum::Sent) || $statuses->contains(SmsMessageStatusEnum::Delivered)) {
            $message->update(['status' => SmsMessageStatusEnum::Sent]);

            return;
        }

        if ($statuses->contains(SmsMessageStatusEnum::Queued)) {
            $message->update(['status' => SmsMessageStatusEnum::Queued]);
        }
    }

    protected function normalizeMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?: $mobile;

        if (str_starts_with($digits, '98') && strlen($digits) > 10) {
            return substr($digits, -10);
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return substr($digits, 1);
        }

        return $digits;
    }
}
