<?php

namespace App\Observers;

use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->syncWallet($transaction);
    }

    public function updated(Transaction $transaction): void
    {
        if (! $transaction->wasChanged(['amount', 'type', 'wallet_id'])) {
            return;
        }

        if ($transaction->wasChanged('wallet_id')) {
            $originalWalletId = (int) $transaction->getOriginal('wallet_id');

            if ($originalWalletId > 0 && $originalWalletId !== (int) $transaction->wallet_id) {
                $this->recalculateWalletById($originalWalletId);
            }
        }

        $this->syncWallet($transaction);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->recalculateWalletById((int) $transaction->wallet_id);
    }

    protected function syncWallet(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $wallet = Wallet::query()->lockForUpdate()->find($transaction->wallet_id);

            if (! $wallet) {
                return;
            }

            $balance = $wallet->recalculateBalance();

            Transaction::withoutEvents(function () use ($transaction, $balance) {
                $transaction->forceFill(['balance_after' => $balance])->saveQuietly();
            });
        });
    }

    protected function recalculateWalletById(int $walletId): void
    {
        DB::transaction(function () use ($walletId) {
            $wallet = Wallet::query()->lockForUpdate()->find($walletId);

            if (! $wallet) {
                return;
            }

            $wallet->recalculateBalance();
        });
    }
}
