<?php

namespace App\Livewire\Forms;

use App\Enums\DepositStatusEnum;
use App\Models\Finance\Deposit;
use App\Models\Finance\Wallet;
use App\Models\User;
use App\Support\PaymentGateways;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class DepositForm extends Form
{
    public ?Deposit $deposit = null;

    public string $user_id = '';

    public string $wallet_id = '';

    public string $amount = '';

    public string $fee = '0';

    public string $tax = '0';

    public string $method = '';

    public string $tracking_code = '';

    public string $status = '';

    public string $admin_note = '';

    public function setDefaults(?User $user = null, ?Wallet $wallet = null): void
    {
        $this->deposit = null;
        $this->user_id = $user ? (string) $user->id : '';
        $this->wallet_id = $wallet ? (string) $wallet->id : '';
        $this->amount = '';
        $this->fee = '0';
        $this->tax = '0';
        $this->method = '';
        $this->tracking_code = '';
        $this->status = DepositStatusEnum::Pending->value;
        $this->admin_note = '';
    }

    public function setModel(Deposit $deposit): void
    {
        $this->deposit = $deposit->loadMissing([
            'user' => fn ($query) => $query->withTrashed(),
            'wallet.currency' => fn ($query) => $query->withTrashed(),
        ]);

        $this->user_id = (string) $deposit->user_id;
        $this->wallet_id = (string) $deposit->wallet_id;
        $this->amount = $this->formatAmountForInput((string) $deposit->amount);
        $this->fee = $this->formatAmountForInput((string) $deposit->fee);
        $this->tax = $this->formatAmountForInput((string) $deposit->tax);
        $this->method = (string) $deposit->method;
        $this->tracking_code = (string) ($deposit->tracking_code ?? '');
        $this->status = $deposit->status instanceof DepositStatusEnum
            ? $deposit->status->value
            : (string) $deposit->status;
        $this->admin_note = (string) ($deposit->admin_note ?? '');
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
        $methods = array_keys(PaymentGateways::depositMethodOptions());

        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'wallet_id' => [
                'required',
                'integer',
                Rule::exists('wallets', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user_id)->whereNull('deleted_at');
                }),
            ],
            'amount' => ['required', 'string'],
            'fee' => ['required', 'string'],
            'tax' => ['required', 'string'],
            'method' => ['required', 'string', Rule::in($methods)],
            'tracking_code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::enum(DepositStatusEnum::class)],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function store(): Deposit
    {
        $this->sanitizeMoneyFields();
        $this->validate();
        $this->validateMoneyValues();

        $amount = $this->normalizeAmount($this->amount);
        $fee = $this->normalizeAmount($this->fee);
        $tax = $this->normalizeAmount($this->tax);

        $this->validateSettledAmount($amount, $fee, $tax);

        return DB::transaction(function () use ($amount, $fee, $tax) {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail((int) $this->wallet_id);

            return Deposit::query()->create([
                'user_id' => (int) $this->user_id,
                'wallet_id' => $wallet->id,
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
        if (! $this->deposit) {
            return;
        }

        $this->sanitizeMoneyFields();
        $this->validate();
        $this->validateMoneyValues();

        $amount = $this->normalizeAmount($this->amount);
        $fee = $this->normalizeAmount($this->fee);
        $tax = $this->normalizeAmount($this->tax);

        $this->validateSettledAmount($amount, $fee, $tax);

        DB::transaction(function () use ($amount, $fee, $tax) {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail((int) $this->wallet_id);
            $deposit = Deposit::query()->lockForUpdate()->findOrFail($this->deposit->id);

            $deposit->update([
                'user_id' => (int) $this->user_id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'fee' => $fee,
                'tax' => $tax,
                'method' => $this->method,
                'tracking_code' => filled($this->tracking_code) ? trim($this->tracking_code) : null,
                'status' => $this->status,
                'admin_note' => filled($this->admin_note) ? trim($this->admin_note) : null,
            ]);

            $this->deposit = $deposit->fresh([
                'user',
                'wallet.currency',
            ]);
        });
    }

    public function destroy(): void
    {
        if (! $this->deposit) {
            return;
        }

        $this->deposit->delete();
        $this->deposit = null;
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

    public function walletOptionLabel(Wallet $wallet): string
    {
        $symbol = $wallet->currency?->symbol ?? __('general.deleted');
        $name = $wallet->currency?->name ?? __('general.deleted');

        return '#'.$wallet->id.' — '.$symbol.' — '.$name;
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

    protected function validateSettledAmount(string $amount, string $fee, string $tax): void
    {
        $decimals = $this->decimals();
        $settled = bcsub(bcsub($amount, $fee, $decimals), $tax, $decimals);

        if (bccomp($settled, '0', $decimals) <= 0) {
            throw ValidationException::withMessages([
                'amount' => __('validation.gt.numeric', [
                    'attribute' => __('general.amount_settled'),
                    'value' => 0,
                ]),
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
