<?php

namespace App\Livewire\Forms;

use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class TransactionForm extends Form
{
    public ?Wallet $wallet = null;

    public ?Transaction $transaction = null;

    public string $type = Transaction::TYPE_CREDIT;

    public string $amount = '';

    public string $description = '';

    public function setWallet(Wallet $wallet): void
    {
        $this->wallet = $wallet->loadMissing([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->transaction = null;
        $this->type = Transaction::TYPE_CREDIT;
        $this->amount = '';
        $this->description = '';
    }

    public function setModel(Transaction $transaction): void
    {
        $this->transaction = $transaction->loadMissing([
            'wallet.currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->wallet = $this->transaction->wallet;
        $this->type = $this->transaction->type;
        $this->amount = $this->formatAmountForInput((string) $this->transaction->amount);
        $this->description = (string) ($this->transaction->description ?? '');
    }

    public function decimals(): int
    {
        return (int) ($this->wallet?->currency?->decimals ?? 8);
    }

    public function amountStep(): string
    {
        $decimals = $this->decimals();

        if ($decimals <= 0) {
            return '1';
        }

        return '0.'.str_repeat('0', $decimals - 1).'1';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $decimals = $this->decimals();

        return [
            'type' => ['required', Rule::in([Transaction::TYPE_CREDIT, Transaction::TYPE_DEBIT])],
            'amount' => ['required', 'numeric', 'gt:0', "decimal:0,{$decimals}"],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function store(): Transaction
    {
        $this->validate();

        if (! $this->wallet) {
            throw ValidationException::withMessages([
                'amount' => __('general.wallet_not_found'),
            ]);
        }

        $amount = $this->normalizeAmount($this->amount);

        return DB::transaction(function () use ($amount) {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail($this->wallet->id);

            $this->ensureSufficientBalance($wallet, $this->type, $amount);
            $this->applyEffect($wallet, $this->type, $amount);

            $transaction = $wallet->transactions()->create([
                'type' => $this->type,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'description' => filled($this->description) ? trim($this->description) : null,
                'reference_type' => Auth::user()?->getMorphClass(),
                'reference_id' => Auth::id(),
            ]);

            $this->wallet = $wallet->fresh(['currency']);

            return $transaction;
        });
    }

    public function update(): void
    {
        $this->validate();

        if (! $this->transaction || ! $this->wallet) {
            return;
        }

        $amount = $this->normalizeAmount($this->amount);

        DB::transaction(function () use ($amount) {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail($this->wallet->id);
            $transaction = Transaction::query()
                ->where('wallet_id', $wallet->id)
                ->lockForUpdate()
                ->findOrFail($this->transaction->id);

            $this->reverseEffect($wallet, $transaction->type, (string) $transaction->amount);

            if (bccomp((string) $wallet->balance, (string) $wallet->locked_balance, 8) < 0) {
                throw ValidationException::withMessages([
                    'amount' => __('general.transaction_update_insufficient_balance'),
                ]);
            }

            $this->ensureSufficientBalance($wallet, $this->type, $amount);
            $this->applyEffect($wallet, $this->type, $amount);

            $transaction->update([
                'type' => $this->type,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'description' => filled($this->description) ? trim($this->description) : null,
            ]);

            $this->transaction = $transaction->fresh();
            $this->wallet = $wallet->fresh(['currency']);
        });
    }

    public function delete(): void
    {
        if (! $this->transaction || ! $this->wallet) {
            return;
        }

        DB::transaction(function () {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail($this->wallet->id);
            $transaction = Transaction::query()
                ->where('wallet_id', $wallet->id)
                ->lockForUpdate()
                ->findOrFail($this->transaction->id);

            $this->reverseEffect($wallet, $transaction->type, (string) $transaction->amount);

            if (bccomp((string) $wallet->balance, (string) $wallet->locked_balance, 8) < 0) {
                throw ValidationException::withMessages([
                    'amount' => __('general.transaction_delete_insufficient_balance'),
                ]);
            }

            $transaction->delete();

            $this->transaction = null;
            $this->wallet = $wallet->fresh(['currency']);
        });
    }

    protected function ensureSufficientBalance(Wallet $wallet, string $type, string $amount): void
    {
        if ($type !== Transaction::TYPE_DEBIT) {
            return;
        }

        $available = bcsub((string) $wallet->balance, (string) $wallet->locked_balance, 8);

        if (bccomp($available, $amount, 8) < 0) {
            throw ValidationException::withMessages([
                'amount' => __('general.insufficient_available_balance'),
            ]);
        }
    }

    protected function applyEffect(Wallet $wallet, string $type, string $amount): void
    {
        if ($type === Transaction::TYPE_CREDIT) {
            $wallet->balance = bcadd((string) $wallet->balance, $amount, 8);
        } else {
            $wallet->balance = bcsub((string) $wallet->balance, $amount, 8);
        }

        $wallet->save();
    }

    protected function reverseEffect(Wallet $wallet, string $type, string $amount): void
    {
        $this->applyEffect(
            $wallet,
            $type === Transaction::TYPE_CREDIT ? Transaction::TYPE_DEBIT : Transaction::TYPE_CREDIT,
            $amount,
        );
    }

    protected function normalizeAmount(string $amount): string
    {
        return bcadd($amount, '0', $this->decimals());
    }

    protected function formatAmountForInput(string $amount): string
    {
        $formatted = bcadd($amount, '0', $this->decimals());

        if ($this->decimals() === 0) {
            return $formatted;
        }

        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    }
}
