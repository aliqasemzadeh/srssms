<?php

namespace App\Services\Sms;

use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use App\Models\Sms\Gateway;
use App\Models\Sms\Message;
use App\Models\User;
use App\Settings\SmsSettings;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SmsBillingService
{
    public function __construct(
        protected SmsPartCounter $partCounter,
    ) {}

    /**
     * @return array{encoding: mixed, parts_count: int, length: int, recipients_count: int, sms_rate: int, cost: int}
     */
    public function estimate(Gateway $gateway, string $text, int $recipientsCount): array
    {
        $analysis = $this->partCounter->analyze($text);
        $rate = (int) ($gateway->sms_rate ?: 0);
        $parts = max(1, (int) $analysis['parts_count']);
        $recipients = max(0, $recipientsCount);

        return [
            'encoding' => $analysis['encoding'],
            'parts_count' => $parts,
            'length' => $analysis['length'],
            'recipients_count' => $recipients,
            'sms_rate' => $rate,
            'cost' => $recipients * $parts * $rate,
        ];
    }

    public function billingCurrencyId(): int
    {
        try {
            $currencyId = app(SmsSettings::class)->billing_currency_id;
        } catch (Throwable) {
            $currencyId = null;
        }

        if (! $currencyId) {
            throw new RuntimeException(__('general.sms_billing_currency_not_configured'));
        }

        return (int) $currencyId;
    }

    public function resolveWallet(User $user): Wallet
    {
        $currencyId = $this->billingCurrencyId();

        $wallet = $user->wallets()
            ->where('is_active', true)
            ->where('currency_id', $currencyId)
            ->with('currency')
            ->first();

        if (! $wallet) {
            throw new RuntimeException(__('general.no_active_wallet'));
        }

        return $wallet;
    }

    public function assertSufficientBalance(User $user, int $cost): Wallet
    {
        $wallet = $this->resolveWallet($user);
        $available = (string) $wallet->available_balance;

        if (bccomp($available, (string) $cost, 8) < 0) {
            throw new RuntimeException(__('general.insufficient_wallet_balance'));
        }

        return $wallet;
    }

    public function debitForMessage(User $user, Message $message, int $cost): Transaction
    {
        return DB::transaction(function () use ($user, $message, $cost): Transaction {
            $currencyId = $this->billingCurrencyId();

            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->where('currency_id', $currencyId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw new RuntimeException(__('general.no_active_wallet'));
            }

            $available = bcsub((string) $wallet->balance, (string) $wallet->locked_balance, 8);

            if (bccomp($available, (string) $cost, 8) < 0) {
                throw new RuntimeException(__('general.insufficient_wallet_balance'));
            }

            $existing = $message->transactions()->lockForUpdate()->first();

            $payload = [
                'wallet_id' => $wallet->id,
                'created_by' => $user->id,
                'type' => Transaction::TYPE_DEBIT,
                'amount' => (string) $cost,
                'description' => __('general.sms_debit_description', ['id' => $message->id]),
            ];

            if ($existing) {
                $existing->update($payload);

                return $existing->fresh();
            }

            return $message->transactions()->create($payload);
        });
    }
}
