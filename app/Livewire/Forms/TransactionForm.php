<?php

namespace App\Livewire\Forms;

use App\Models\Finance\Currency;
use App\Models\Finance\Transaction;
use App\Models\Finance\Wallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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

    public string $reference_type = '';

    public string $reference_id = '';

    public function setWallet(Wallet $wallet): void
    {
        $this->wallet = $wallet->loadMissing([
            'currency' => fn ($query) => $query->withTrashed(),
        ]);
        $this->transaction = null;
        $this->type = Transaction::TYPE_CREDIT;
        $this->amount = '';
        $this->description = '';
        $this->reference_type = '';
        $this->reference_id = '';
    }

    public function setModel(Transaction $transaction): void
    {
        $this->transaction = $transaction->loadMissing([
            'wallet.currency' => fn ($query) => $query->withTrashed(),
            'creator',
            'reference',
        ]);
        $this->wallet = $this->transaction->wallet;
        $this->type = $this->transaction->type;
        $this->amount = $this->formatAmountForInput((string) $this->transaction->amount);
        $this->description = (string) ($this->transaction->description ?? '');
        $this->reference_type = $this->resolveReferenceTypeKey($this->transaction->reference_type);
        $this->reference_id = $this->transaction->reference_id ? (string) $this->transaction->reference_id : '';
    }

    public function decimals(): int
    {
        return (int) ($this->wallet?->currency?->decimals ?? 8);
    }

    public function moneyMaskExpression(): string
    {
        return '$money($input, \'.\', \',\', '.$this->decimals().')';
    }

    /**
     * @deprecated Use moneyMaskExpression()
     */
    public function moneyMask(): string
    {
        return $this->moneyMaskExpression();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $decimals = $this->decimals();
        $referenceTypes = array_keys(Transaction::referenceTypes());

        return [
            'type' => ['required', Rule::in([Transaction::TYPE_CREDIT, Transaction::TYPE_DEBIT])],
            'amount' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
            'reference_type' => ['nullable', 'string', Rule::in($referenceTypes)],
            'reference_id' => [
                Rule::requiredIf(fn () => filled($this->reference_type)),
                'nullable',
                'integer',
            ],
        ];
    }

    public function store(): Transaction
    {
        $this->amount = $this->sanitizeAmount($this->amount);
        $this->validate();
        $this->validateAmountValue();
        $this->validateReference();

        if (! $this->wallet) {
            throw ValidationException::withMessages([
                'amount' => __('general.wallet_not_found'),
            ]);
        }

        $amount = $this->normalizeAmount($this->amount);
        [$referenceType, $referenceId] = $this->resolveReference();

        return DB::transaction(function () use ($amount, $referenceType, $referenceId) {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail($this->wallet->id);

            $this->ensureSufficientBalance($wallet, $this->type, $amount);
            $this->applyEffect($wallet, $this->type, $amount);

            $transaction = $wallet->transactions()->create([
                'created_by' => Auth::id(),
                'type' => $this->type,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'description' => filled($this->description) ? trim($this->description) : null,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            $this->wallet = $wallet->fresh(['currency']);

            return $transaction;
        });
    }

    public function update(): void
    {
        $this->amount = $this->sanitizeAmount($this->amount);
        $this->validate();
        $this->validateAmountValue();
        $this->validateReference();

        if (! $this->transaction || ! $this->wallet) {
            return;
        }

        $amount = $this->normalizeAmount($this->amount);
        [$referenceType, $referenceId] = $this->resolveReference();

        DB::transaction(function () use ($amount, $referenceType, $referenceId) {
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
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            $this->transaction = $transaction->fresh(['creator', 'reference']);
            $this->wallet = $wallet->fresh(['currency']);
        });
    }

    public function destroyTransaction(): void
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

    /**
     * @return Collection<int, Model>
     */
    public function referenceOptions(string $search = ''): Collection
    {
        $class = Transaction::referenceTypes()[$this->reference_type] ?? null;

        if (! $class) {
            return new Collection;
        }

        $query = $class::query();

        if ($class === User::class) {
            $query->withTrashed()
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
                })
                ->orderBy('first_name')
                ->limit(20);

            $results = $query->get();

            if (filled($this->reference_id) && ! $results->pluck('id')->contains((int) $this->reference_id)) {
                $selected = User::withTrashed()->find($this->reference_id);
                if ($selected) {
                    $results = $results->prepend($selected);
                }
            }

            return $results;
        }

        if ($class === Wallet::class) {
            $query->withTrashed()
                ->with([
                    'user' => fn ($q) => $q->withTrashed(),
                    'currency' => fn ($q) => $q->withTrashed(),
                ])
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('id', $search)
                            ->orWhereHas('user', function ($query) use ($search) {
                                $query->withTrashed()
                                    ->where(function ($query) use ($search) {
                                        $query->where('first_name', 'like', "%{$search}%")
                                            ->orWhere('last_name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");
                                    });
                            })
                            ->orWhereHas('currency', function ($query) use ($search) {
                                $query->withTrashed()
                                    ->where(function ($query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('symbol', 'like', "%{$search}%");
                                    });
                            });
                    });
                })
                ->latest('id')
                ->limit(20);

            $results = $query->get();

            if (filled($this->reference_id) && ! $results->pluck('id')->contains((int) $this->reference_id)) {
                $selected = Wallet::withTrashed()
                    ->with([
                        'user' => fn ($q) => $q->withTrashed(),
                        'currency' => fn ($q) => $q->withTrashed(),
                    ])
                    ->find($this->reference_id);

                if ($selected) {
                    $results = $results->prepend($selected);
                }
            }

            return $results;
        }

        if ($class === Currency::class) {
            $query->withTrashed()
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('symbol', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->limit(20);

            $results = $query->get();

            if (filled($this->reference_id) && ! $results->pluck('id')->contains((int) $this->reference_id)) {
                $selected = Currency::withTrashed()->find($this->reference_id);
                if ($selected) {
                    $results = $results->prepend($selected);
                }
            }

            return $results;
        }

        return new Collection;
    }

    public function referenceOptionLabel(Model $model): string
    {
        if ($model instanceof User) {
            return trim($model->full_name.' — '.($model->email ?: $model->username ?: '#'.$model->id));
        }

        if ($model instanceof Wallet) {
            $userName = $model->user?->full_name ?? __('general.deleted');
            $symbol = $model->currency?->symbol ?? __('general.deleted');

            return '#'.$model->id.' — '.$userName.' — '.$symbol;
        }

        if ($model instanceof Currency) {
            return $model->symbol.' — '.$model->name;
        }

        return class_basename($model).' #'.$model->getKey();
    }

    protected function validateAmountValue(): void
    {
        $decimals = $this->decimals();
        $amount = $this->sanitizeAmount($this->amount);

        if ($amount === '' || ! is_numeric($amount) || bccomp($amount, '0', $decimals) <= 0) {
            throw ValidationException::withMessages([
                'amount' => __('validation.gt.numeric', ['attribute' => __('validation.attributes.amount'), 'value' => 0]),
            ]);
        }

        if (str_contains($amount, '.')) {
            $fraction = explode('.', $amount, 2)[1] ?? '';
            if (strlen($fraction) > $decimals) {
                throw ValidationException::withMessages([
                    'amount' => __('general.amount_decimals_hint', ['decimals' => $decimals]),
                ]);
            }
        }
    }

    protected function validateReference(): void
    {
        if (blank($this->reference_type)) {
            $this->reference_id = '';

            return;
        }

        $class = Transaction::referenceTypes()[$this->reference_type] ?? null;

        if (! $class || blank($this->reference_id) || ! $class::query()->withTrashed()->whereKey($this->reference_id)->exists()) {
            throw ValidationException::withMessages([
                'reference_id' => __('general.reference_not_found'),
            ]);
        }
    }

    /**
     * @return array{0: ?string, 1: ?int}
     */
    protected function resolveReference(): array
    {
        if (blank($this->reference_type) || blank($this->reference_id)) {
            return [null, null];
        }

        $class = Transaction::referenceTypes()[$this->reference_type] ?? null;

        if (! $class) {
            return [null, null];
        }

        return [(new $class)->getMorphClass(), (int) $this->reference_id];
    }

    protected function resolveReferenceTypeKey(?string $morphClass): string
    {
        if (blank($morphClass)) {
            return '';
        }

        foreach (Transaction::referenceTypes() as $key => $class) {
            if ($morphClass === $class || $morphClass === (new $class)->getMorphClass()) {
                return $key;
            }
        }

        return '';
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

    protected function sanitizeAmount(string $amount): string
    {
        $amount = str_replace([',', ' ', '٬'], '', $amount);
        $amount = str_replace('٫', '.', $amount);

        return trim($amount);
    }

    protected function normalizeAmount(string $amount): string
    {
        return bcadd($this->sanitizeAmount($amount), '0', $this->decimals());
    }

    protected function formatAmountForInput(string $amount): string
    {
        $formatted = bcadd($amount, '0', $this->decimals());

        if ($this->decimals() === 0) {
            return number_format((float) $formatted, 0, '.', ',');
        }

        return number_format((float) $formatted, $this->decimals(), '.', ',');
    }
}
