<?php

namespace App\Livewire\Forms;

use App\Models\Finance\Currency;
use App\Models\Finance\Wallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class WalletForm extends Form
{
    public ?User $user = null;

    public string $currency_id = '';

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->currency_id = '';
    }

    public function rules(): array
    {
        return [
            'currency_id' => [
                'required',
                'integer',
                Rule::exists('currencies', 'id')->where(fn ($query) => $query->whereNull('deleted_at')->where('is_active', true)),
                Rule::unique('wallets', 'currency_id')
                    ->where(fn ($query) => $query->where('user_id', $this->user?->id)->whereNull('deleted_at')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'currency_id.unique' => __('general.wallet_already_exists'),
        ];
    }

    public function store(): Wallet
    {
        $this->validate();

        $existing = Wallet::withTrashed()
            ->where('user_id', $this->user->id)
            ->where('currency_id', $this->currency_id)
            ->first();

        if ($existing?->trashed()) {
            $existing->restore();
            $existing->update([
                'balance' => 0,
                'locked_balance' => 0,
                'is_active' => true,
            ]);

            return $existing->fresh(['currency']);
        }

        return Wallet::query()->create([
            'user_id' => $this->user->id,
            'currency_id' => (int) $this->currency_id,
            'balance' => 0,
            'locked_balance' => 0,
            'is_active' => true,
        ])->load('currency');
    }

    /**
     * @return Collection<int, Currency>
     */
    public function availableCurrencies(): Collection
    {
        $ownedCurrencyIds = Wallet::query()
            ->where('user_id', $this->user->id)
            ->pluck('currency_id');

        return Currency::query()
            ->where('is_active', true)
            ->whereNotIn('id', $ownedCurrencyIds)
            ->orderBy('name')
            ->get();
    }
}
