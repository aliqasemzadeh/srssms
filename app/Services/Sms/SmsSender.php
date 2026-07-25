<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender as SmsSenderContract;
use App\Enums\Sms\SmsDirectionEnum;
use App\Enums\Sms\SmsMessageStatusEnum;
use App\Jobs\Sms\SendSmsCampaignJob;
use App\Models\Phonebook\Contact;
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
        protected SmsBillingService $billing,
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
                'sms_rate' => (int) ($gateway->sms_rate ?: 0),
                'cost' => null,
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

    /**
     * Queue a billed outbound campaign for a panel user.
     *
     * @param  array<int, array{mobile: string, contact_id?: int|null}>  $recipients
     */
    public function queueCampaign(
        Gateway $gateway,
        User $user,
        string $text,
        array $recipients,
        bool $bill = true,
    ): Message {
        $gateway->loadMissing('provider');

        if (! $gateway->is_active || ! $gateway->provider?->is_active) {
            throw new RuntimeException(__('general.sms_gateway_inactive'));
        }

        $normalized = collect($recipients)
            ->map(fn (array $row) => [
                'mobile' => trim((string) ($row['mobile'] ?? '')),
                'contact_id' => $row['contact_id'] ?? null,
            ])
            ->filter(fn (array $row) => $row['mobile'] !== '')
            ->unique('mobile')
            ->values();

        if ($normalized->isEmpty()) {
            throw new RuntimeException(__('general.no_sms_recipients'));
        }

        $estimate = $this->billing->estimate($gateway, $text, $normalized->count());
        $analysis = $this->partCounter->analyze($text);

        return DB::transaction(function () use ($gateway, $user, $text, $normalized, $estimate, $analysis, $bill): Message {
            if ($bill) {
                $this->billing->assertSufficientBalance($user, $estimate['cost']);
            }

            $message = Message::query()->create([
                'gateway_id' => $gateway->id,
                'user_id' => $user->id,
                'direction' => SmsDirectionEnum::Outbound,
                'number' => $gateway->number,
                'body' => $text,
                'parts_count' => $analysis['parts_count'],
                'sms_rate' => $estimate['sms_rate'],
                'cost' => $bill ? $estimate['cost'] : null,
                'encoding' => $analysis['encoding'],
                'status' => SmsMessageStatusEnum::Queued,
            ]);

            foreach ($normalized as $row) {
                $contactId = $row['contact_id'];

                if ($contactId) {
                    $owns = Contact::query()
                        ->ownedBy($user)
                        ->whereKey($contactId)
                        ->exists();

                    if (! $owns) {
                        $contactId = null;
                    }
                }

                $message->recipients()->create([
                    'contact_id' => $contactId,
                    'mobile' => $row['mobile'],
                    'status' => SmsMessageStatusEnum::Queued,
                ]);
            }

            if ($bill) {
                $this->billing->debitForMessage($user, $message, $estimate['cost']);
            }

            SendSmsCampaignJob::dispatch($message->id);

            return $message->fresh(['recipients']);
        });
    }

    /**
     * Re-queue a failed outbound message (or only its failed recipients). Does not re-bill.
     */
    public function resend(Message $message): Message
    {
        $message->loadMissing(['gateway.provider', 'recipients']);

        if ($message->direction !== SmsDirectionEnum::Outbound) {
            throw new RuntimeException(__('general.sms_resend_not_allowed'));
        }

        $gateway = $message->gateway;

        if (! $gateway || ! $gateway->is_active || ! $gateway->provider?->is_active) {
            throw new RuntimeException(__('general.sms_gateway_inactive'));
        }

        $failedRecipients = $message->recipients
            ->filter(fn ($recipient) => $recipient->status === SmsMessageStatusEnum::Failed)
            ->values();

        if ($failedRecipients->isEmpty()) {
            if ($message->status !== SmsMessageStatusEnum::Failed || $message->recipients->isEmpty()) {
                throw new RuntimeException(__('general.sms_resend_not_allowed'));
            }

            $failedRecipients = $message->recipients->values();
        }

        DB::transaction(function () use ($message, $failedRecipients): void {
            foreach ($failedRecipients as $recipient) {
                $recipient->update([
                    'status' => SmsMessageStatusEnum::Queued,
                    'reference_id' => null,
                    'error' => null,
                    'delivered_at' => null,
                ]);
            }

            $message->update([
                'status' => SmsMessageStatusEnum::Queued,
            ]);
        });

        SendSmsCampaignJob::dispatch($message->id);

        return $message->fresh(['recipients', 'gateway.provider', 'user']);
    }

    public function defaultGateway(?User $user = null): Gateway
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
                ->when($user, fn ($query) => $query->usableBy($user))
                ->first();

            if ($gateway) {
                return $gateway;
            }
        }

        $query = Gateway::query()
            ->with('provider')
            ->where('is_active', true)
            ->whereHas('provider', fn ($q) => $q->where('is_active', true));

        if ($user) {
            $gateway = (clone $query)->usableBy($user)->orderBy('id')->first();
            if ($gateway) {
                return $gateway;
            }
        }

        $gateway = (clone $query)->public()->orderBy('id')->first()
            ?? $query->orderBy('id')->first();

        if (! $gateway) {
            throw new RuntimeException('No active SMS gateway is configured.');
        }

        return $gateway;
    }
}
