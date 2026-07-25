<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender as SmsSenderContract;
use App\Enums\Sms\SmsDirectionEnum;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Models\Sms\Gateway;
use App\Models\Sms\Message;
use App\Models\User;
use App\Settings\SmsSettings;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SmsSender implements SmsSenderContract
{
    public function __construct(
        protected SmsManager $manager,
        protected SmsPartCounter $partCounter,
    ) {}

    public function send(string $mobile, string $message): void
    {
        try {
            $gateway = $this->defaultGateway();
        } catch (RuntimeException) {
            app(LogSmsSender::class)->send($mobile, $message);

            return;
        }

        $this->sendViaGateway($gateway, [$mobile], $message);
    }

    /**
     * @param  array<int, string>  $mobiles
     */
    public function sendViaGateway(
        Gateway $gateway,
        array $mobiles,
        string $text,
        ?User $user = null,
    ): Message {
        $gateway->loadMissing('provider');

        if (! $gateway->is_active || ! $gateway->provider?->is_active) {
            throw new RuntimeException('SMS gateway or provider is inactive.');
        }

        $mobiles = array_values(array_unique(array_filter(array_map('trim', $mobiles))));

        if ($mobiles === []) {
            throw new RuntimeException('No recipients provided for SMS.');
        }

        $analysis = $this->partCounter->analyze($text);

        return DB::transaction(function () use ($gateway, $mobiles, $text, $user, $analysis): Message {
            $message = Message::query()->create([
                'gateway_id' => $gateway->id,
                'user_id' => $user?->id,
                'direction' => SmsDirectionEnum::Outbound,
                'number' => $gateway->number,
                'body' => $text,
                'parts_count' => $analysis['parts_count'],
                'encoding' => $analysis['encoding'],
                'status' => SmsMessageStatusEnum::Pending,
                'sent_at' => now(),
            ]);

            foreach ($mobiles as $mobile) {
                $message->recipients()->create([
                    'mobile' => $mobile,
                    'status' => SmsMessageStatusEnum::Pending,
                ]);
            }

            $result = $this->manager->driverFor($gateway)->send($gateway, $mobiles, $text);

            $message->provider_payload = is_array($result->raw) ? $result->raw : ['raw' => $result->raw];

            $hasFailure = false;
            $hasSuccess = false;

            foreach ($result->recipients as $recipientResult) {
                $recipient = $message->recipients()
                    ->where('mobile', $recipientResult['mobile'])
                    ->first();

                if (! $recipient) {
                    continue;
                }

                $status = SmsMessageStatusEnum::tryFrom($recipientResult['status'])
                    ?? SmsMessageStatusEnum::Failed;

                $recipient->update([
                    'status' => $status,
                    'reference_id' => $recipientResult['reference_id'],
                    'error' => $recipientResult['error'],
                    'delivered_at' => $status === SmsMessageStatusEnum::Delivered ? now() : null,
                ]);

                if ($status === SmsMessageStatusEnum::Failed) {
                    $hasFailure = true;
                } else {
                    $hasSuccess = true;
                }
            }

            $message->status = match (true) {
                $hasSuccess && ! $hasFailure => SmsMessageStatusEnum::Sent,
                $hasSuccess && $hasFailure => SmsMessageStatusEnum::Sent,
                default => SmsMessageStatusEnum::Failed,
            };
            $message->save();

            return $message->fresh(['recipients']);
        });
    }

    protected function defaultGateway(): Gateway
    {
        try {
            $settings = app(SmsSettings::class);
            $gatewayId = $settings->default_gateway_id;
        } catch (Throwable) {
            $gatewayId = null;
        }

        if ($gatewayId) {
            $gateway = Gateway::query()
                ->with('provider')
                ->whereKey($gatewayId)
                ->where('is_active', true)
                ->first();

            if ($gateway) {
                return $gateway;
            }
        }

        $gateway = Gateway::query()
            ->with('provider')
            ->where('is_active', true)
            ->whereHas('provider', fn ($query) => $query->where('is_active', true))
            ->public()
            ->orderBy('id')
            ->first();

        if (! $gateway) {
            $gateway = Gateway::query()
                ->with('provider')
                ->where('is_active', true)
                ->whereHas('provider', fn ($query) => $query->where('is_active', true))
                ->orderBy('id')
                ->first();
        }

        if (! $gateway) {
            throw new RuntimeException('No active SMS gateway is configured.');
        }

        return $gateway;
    }
}
