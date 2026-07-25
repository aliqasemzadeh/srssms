<?php

namespace App\Observers;

use App\Enums\WithdrawalStatusEnum;
use App\Models\Finance\Transaction;
use App\Models\Finance\Withdrawal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalObserver
{
    public function created(Withdrawal $withdrawal): void
    {
        $this->syncTransaction($withdrawal);
    }

    public function updated(Withdrawal $withdrawal): void
    {
        if (! $withdrawal->wasChanged(['status', 'amount', 'wallet_id'])) {
            return;
        }

        $this->syncTransaction($withdrawal);
    }

    public function deleted(Withdrawal $withdrawal): void
    {
        $this->deleteLinkedTransactions($withdrawal);
    }

    public function forceDeleted(Withdrawal $withdrawal): void
    {
        $this->deleteLinkedTransactions($withdrawal);
    }

    protected function syncTransaction(Withdrawal $withdrawal): void
    {
        if ($withdrawal->status === WithdrawalStatusEnum::Approved) {
            $this->ensureDebitTransaction($withdrawal);

            return;
        }

        $this->deleteLinkedTransactions($withdrawal);
    }

    protected function ensureDebitTransaction(Withdrawal $withdrawal): void
    {
        DB::transaction(function () use ($withdrawal) {
            $existing = $withdrawal->transactions()->lockForUpdate()->first();

            $payload = [
                'wallet_id' => $withdrawal->wallet_id,
                'created_by' => Auth::id() ?? $withdrawal->created_by,
                'type' => Transaction::TYPE_DEBIT,
                'amount' => (string) $withdrawal->amount,
                'description' => __('general.withdrawal').' #'.$withdrawal->id,
            ];

            if ($existing) {
                $existing->update($payload);

                return;
            }

            $withdrawal->transactions()->create($payload);
        });
    }

    protected function deleteLinkedTransactions(Withdrawal $withdrawal): void
    {
        DB::transaction(function () use ($withdrawal) {
            $withdrawal->transactions()->get()->each->delete();
        });
    }
}
