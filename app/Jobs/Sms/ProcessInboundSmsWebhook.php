<?php

namespace App\Jobs\Sms;

use App\Enums\Sms\SmsDirectionEnum;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Gateway;
use App\Models\Sms\Message;
use App\Services\Sms\SmsPartCounter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessInboundSmsWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $providerDriver,
        public array $payload,
    ) {}

    public function handle(SmsPartCounter $partCounter): void
    {
        $from = (string) ($this->payload['from'] ?? '');
        $gatewayNumber = (string) ($this->payload['gateway'] ?? '');
        $text = (string) ($this->payload['text'] ?? '');
        $referenceId = isset($this->payload['reference_id'])
            ? (string) $this->payload['reference_id']
            : null;

        if ($from === '' || $gatewayNumber === '') {
            return;
        }

        $gateway = Gateway::query()
            ->with('provider')
            ->where('number', $gatewayNumber)
            ->where('is_active', true)
            ->whereHas('provider', function ($query) {
                $query->where('driver', $this->providerDriver)->where('is_active', true);
            })
            ->first();

        if (! $gateway) {
            return;
        }

        if ($referenceId) {
            $exists = Message::query()
                ->where('gateway_id', $gateway->id)
                ->where('direction', SmsDirectionEnum::Inbound)
                ->where('reference_id', $referenceId)
                ->exists();

            if ($exists) {
                return;
            }
        }

        $analysis = $partCounter->analyze($text);

        Message::query()->create([
            'gateway_id' => $gateway->id,
            'user_id' => null,
            'direction' => SmsDirectionEnum::Inbound,
            'number' => $from,
            'body' => $text,
            'parts_count' => $analysis['parts_count'],
            'encoding' => $analysis['encoding'],
            'status' => SmsMessageStatusEnum::Received,
            'reference_id' => $referenceId,
            'provider_payload' => $this->payload,
            'received_at' => now(),
        ]);
    }
}
