<?php

namespace App\Livewire\Forms;

use App\Enums\WithdrawalStatusEnum;
use App\Models\Finance\Wallet;
use App\Models\Finance\Withdrawal;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class WithdrawalForm extends Form
{
    public ?Withdrawal $withdrawal = null;

    public string $user_id = '';

    public string $wallet_id = '';

    public string $user_account_id = '';

    public string $amount = '';

    public string $fee = '0';

    public string $tax = '0';

    public string $method = '';

    public string $tracking_code = '';

    public string $status = '';

    public string $admin_note = '';

    public function setDefaults(?User $user = null, ?Wallet $wallet = null): void
    {
        $this->withdrawal = null;
        $this->user_id = $user ? (string) $user->id : '';
        $this->wallet_id = $wallet ? (string) $wallet->id : '';
        $this->user_account_id = '';
        $this->amount = '';
        $this->fee = '0';
        $this->tax = '0';
        $this->method = '';
        $this->tracking_code = '';
        $this->status = WithdrawalStatusEnum::Pending->value;
        $this->admin_note = '';
    }

    public function setModel(Withdrawal $withdrawal): void
    {
        $this->withdrawal = $withdrawal->loadMissing([
            'user' => fn ($query) => $query->withTrashed(),
            'wallet.currency' => fn ($query) => $query->withTrashed(),
            'userAccount' => fn ($query) => $query->withTrashed(),
        ]);

        $this->user_id = (string) $withdrawal->user_id;
        $this->wallet_id = (string) $withdrawal->wallet_id;
        $this->user_account_id = (string) $withdrawal->user_account_id;
        $this->amount = $this->formatAmountForInput((string) $withdrawal->amount);
        $this->fee = $this->formatAmountForInput((string) $withdrawal->fee);
        $this->tax = $this->formatAmountForInput((string) $withdrawal->tax);
        $this->method = (string) $withdrawal->method;
        $this->tracking_code = (string) ($withdrawal->tracking_code ?? '');
        $this->status = $withdrawal->status instanceof WithdrawalStatusEnum
            ? $withdrawal->status->value
            : (string) $withdrawal->status;
        $this->admin_note = (string) ($withdrawal->admin_note ?? '');
    }

    public function decimals(): int
    {
        $wallet = $this->resolveWallet();

        return (int) ($wallet?->currency?->decimals ?? 8);
    }

    public function moneyMaskExpression(): string
    {
        return '$money($input, \'.\', \',\', '.$this->decimals().')';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $methods = array_keys(config('finance.withdrawal_methods', []));
        $wallet = $this->resolveWallet();

        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'wallet_id' => [
                'required',
                'integer',
                Rule::exists('wallets', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user_id)->whereNull('deleted_at');
                }),
            ],
            'user_account_id' => [
                'required',
                'integer',
                Rule::exists('user_accounts', 'id')->where(function ($query) use ($wallet) {
                    $query->where('user_id', $this->user_id)
                        ->whereNull('deleted_at')
                        ->when($wallet, fn ($query) => $query->where('currency_id', $wallet->currency_id));
                }),
            ],
            'amount' => ['required', 'string'],
            'fee' => ['required', 'string'],
            'tax' => ['required', 'string'],
            'method' => ['required', 'string', Rule::in($methods)],
            'tracking_code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::enum(WithdrawalStatusEnum::class)],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function store(): Withdrawal
    {
        $this->sanitizeMoneyFields();
        $this->validate();
        $this->validateMoneyValues();

        $amount = $this->normalizeAmount($this->amount);
        $fee = $this->normalizeAmount($this->fee);
        $tax = $this->normalizeAmount($this->tax);

        return DB::transaction(function () use ($amount, $fee, $tax) {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail((int) $this->wallet_id);

            if ($this->status === WithdrawalStatusEnum::Approved->value) {
                $this->ensureSufficientBalance($wallet, $amount);
            }

            return Withdrawal::query()->create([
                'user_id' => (int) $this->user_id,
                'wallet_id' => $wallet->id,
                'user_account_id' => (int) $this->user_account_id,
                'created_by' => Auth::id(),
                'amount' => $amount,
                'fee' => $fee,
                'tax' => $tax,
                'method' => $this->method,
                'tracking_code' => filled($this->tracking_code) ? trim($this->tracking_code) : null,
                'status' => $this->status,
                'ip_address' => Request::ip(),
                'admin_note' => filled($this->admin_note) ? trim($this->admin_note) : null,
            ]);
        });
    }

    public function update(): void
    {
        if (! $this->withdrawal) {
            return;
        }

        $this->sanitizeMoneyFields();
        $this->validate();
        $this->validateMoneyValues();

        $amount = $this->normalizeAmount($this->amount);
        $fee = $this->normalizeAmount($this->fee);
        $tax = $this->normalizeAmount($this->tax);

        DB::transaction(function () use ($amount, $fee, $tax) {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail((int) $this->wallet_id);
            $withdrawal = Withdrawal::query()->lockForUpdate()->findOrFail($this->withdrawal->id);

            $wasApproved = $withdrawal->status === WithdrawalStatusEnum::Approved;
            $willBeApproved = $this->status === WithdrawalStatusEnum::Approved->value;

            if ($willBeApproved) {
                $available = bcsub((string) $wallet->balance, (string) $wallet->locked_balance, 8);

                if ($wasApproved) {
                    $available = bcadd($available, (string) $withdrawal->amount, 8);
                }

                if (bccomp($available, $amount, 8) < 0) {
                    throw ValidationException::withMessages([
                        'amount' => __('general.insufficient_available_balance'),
                    ]);
                }
            }

            $withdrawal->update([
                'user_id' => (int) $this->user_id,
                'wallet_id' => $wallet->id,
                'user_account_id' => (int) $this->user_account_id,
                'amount' => $amount,
                'fee' => $fee,
                'tax' => $tax,
                'method' => $this->method,
                'tracking_code' => filled($this->tracking_code) ? trim($this->tracking_code) : null,
                'status' => $this->status,
                'admin_note' => filled($this->admin_note) ? trim($this->admin_note) : null,
            ]);

            $this->withdrawal = $withdrawal->fresh([
                'user',
                'wallet.currency',
                'userAccount',
            ]);
        });
    }

    public function destroy(): void
    {
        if (! $this->withdrawal) {
            return;
        }

        $this->withdrawal->delete();
        $this->withdrawal = null;
    }

    /**
     * @return Collection<int, User>
     */
    public function userOptions(string $search = ''): Collection
    {
        $results = User::query()
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
            ->limit(20)
            ->get();

        if (filled($this->user_id) && ! $results->pluck('id')->contains((int) $this->user_id)) {
            $selected = User::withTrashed()->find($this->user_id);
            if ($selected) {
                $results = $results->prepend($selected);
            }
        }

        return $results;
    }

    /**
     * @return Collection<int, Wallet>
     */
    public function walletOptions(string $search = ''): Collection
    {
        if (blank($this->user_id)) {
            return new Collection;
        }

        $results = Wallet::query()
            ->where('user_id', $this->user_id)
            ->with(['currency' => fn ($query) => $query->withTrashed()])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', $search)
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
            ->limit(20)
            ->get();

        if (filled($this->wallet_id) && ! $results->pluck('id')->contains((int) $this->wallet_id)) {
            $selected = Wallet::withTrashed()
                ->with(['currency' => fn ($query) => $query->withTrashed()])
                ->find($this->wallet_id);

            if ($selected) {
                $results = $results->prepend($selected);
            }
        }

        return $results;
    }

    /**
     * @return Collection<int, UserAccount>
     */
    public function userAccountOptions(string $search = ''): Collection
    {
        if (blank($this->user_id)) {
            return new Collection;
        }

        $wallet = $this->resolveWallet();

        $results = UserAccount::query()
            ->where('user_id', $this->user_id)
            ->when($wallet, fn ($query) => $query->where('currency_id', $wallet->currency_id))
            ->with(['currency' => fn ($query) => $query->withTrashed()])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('account_number', 'like', "%{$search}%")
                        ->orWhere('account_owner', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->limit(20)
            ->get();

        if (filled($this->user_account_id) && ! $results->pluck('id')->contains((int) $this->user_account_id)) {
            $selected = UserAccount::withTrashed()
                ->with(['currency' => fn ($query) => $query->withTrashed()])
                ->find($this->user_account_id);

            if ($selected) {
                $results = $results->prepend($selected);
            }
        }

        return $results;
    }

    public function walletOptionLabel(Wallet $wallet): string
    {
        $symbol = $wallet->currency?->symbol ?? __('general.deleted');
        $name = $wallet->currency?->name ?? __('general.deleted');

        return '#'.$wallet->id.' — '.$symbol.' — '.$name;
    }

    public function userAccountOptionLabel(UserAccount $account): string
    {
        $owner = $account->account_owner ? $account->account_owner.' · ' : '';

        return $owner.$account->account_number;
    }

    protected function resolveWallet(): ?Wallet
    {
        if (blank($this->wallet_id)) {
            return null;
        }

        return Wallet::query()
            ->with(['currency' => fn ($query) => $query->withTrashed()])
            ->find($this->wallet_id);
    }

    protected function sanitizeMoneyFields(): void
    {
        $this->amount = $this->sanitizeAmount($this->amount);
        $this->fee = $this->sanitizeAmount($this->fee);
        $this->tax = $this->sanitizeAmount($this->tax);
    }

    protected function validateMoneyValues(): void
    {
        $decimals = $this->decimals();

        foreach (['amount' => $this->amount, 'fee' => $this->fee, 'tax' => $this->tax] as $field => $value) {
            $attribute = __('general.'.$field);

            if ($value === '' || ! is_numeric($value)) {
                throw ValidationException::withMessages([
                    $field => __('validation.numeric', ['attribute' => $attribute]),
                ]);
            }

            if ($field === 'amount' && bccomp($value, '0', $decimals) <= 0) {
                throw ValidationException::withMessages([
                    $field => __('validation.gt.numeric', ['attribute' => $attribute, 'value' => 0]),
                ]);
            }

            if ($field !== 'amount' && bccomp($value, '0', $decimals) < 0) {
                throw ValidationException::withMessages([
                    $field => __('validation.min.numeric', ['attribute' => $attribute, 'min' => 0]),
                ]);
            }

            if (str_contains($value, '.')) {
                $fraction = explode('.', $value, 2)[1] ?? '';
                if (strlen($fraction) > $decimals) {
                    throw ValidationException::withMessages([
                        $field => __('general.amount_decimals_hint', ['decimals' => $decimals]),
                    ]);
                }
            }
        }
    }

    protected function ensureSufficientBalance(Wallet $wallet, string $amount): void
    {
        $available = bcsub((string) $wallet->balance, (string) $wallet->locked_balance, 8);

        if (bccomp($available, $amount, 8) < 0) {
            throw ValidationException::withMessages([
                'amount' => __('general.insufficient_available_balance'),
            ]);
        }
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
