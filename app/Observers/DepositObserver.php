<?php

namespace App\Observers;

use App\Enums\DepositStatusEnum;
use App\Models\Finance\Deposit;
use App\Models\Finance\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositObserver
{
    public function created(Deposit $deposit): void
    {
        $this->syncTransaction($deposit);
    }

    public function updated(Deposit $deposit): void
    {
        if (! $deposit->wasChanged(['status', 'amount', 'fee', 'tax', 'wallet_id'])) {
            return;
        }

        $this->syncTransaction($deposit);
    }

    public function deleted(Deposit $deposit): void
    {
        $this->deleteLinkedTransactions($deposit);
    }

    public function forceDeleted(Deposit $deposit): void
    {
        $this->deleteLinkedTransactions($deposit);
    }

    protected function syncTransaction(Deposit $deposit): void
    {
        if ($deposit->status === DepositStatusEnum::Approved) {
            $this->ensureCreditTransaction($deposit);

            return;
        }

        $this->deleteLinkedTransactions($deposit);
    }

    protected function ensureCreditTransaction(Deposit $deposit): void
    {
        DB::transaction(function () use ($deposit) {
            $existing = $deposit->transactions()->lockForUpdate()->first();

            $payload = [
                'wallet_id' => $deposit->wallet_id,
                'created_by' => Auth::id() ?? $deposit->created_by,
                'type' => Transaction::TYPE_CREDIT,
                'amount' => $deposit->settledAmount(),
                'description' => __('general.deposit').' #'.$deposit->id,
            ];

            if ($existing) {
                $existing->update($payload);

                return;
            }

            $deposit->transactions()->create($payload);
        });
    }

    protected function deleteLinkedTransactions(Deposit $deposit): void
    {
        DB::transaction(function () use ($deposit) {
            $deposit->transactions()->get()->each->delete();
        });
    }
}
