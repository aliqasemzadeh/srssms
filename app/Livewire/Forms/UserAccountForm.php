<?php

namespace App\Livewire\Forms;

use App\Enums\UserAccountTypeEnum;
use App\Models\Finance\Currency;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserAccountForm extends Form
{
    public ?User $user = null;

    public ?UserAccount $userAccount = null;

    public string $currency_id = '';

    public string $type = '';

    public string $account_number = '';

    public string $account_owner = '';

    public string $status = UserAccount::STATUS_PENDING;

    public bool $is_active = true;

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->userAccount = null;
        $this->currency_id = '';
        $this->type = '';
        $this->account_number = '';
        $this->account_owner = '';
        $this->status = UserAccount::STATUS_PENDING;
        $this->is_active = true;
    }

    public function setModel(UserAccount $userAccount): void
    {
        $this->userAccount = $userAccount;
        $this->user = $userAccount->user;
        $this->currency_id = (string) $userAccount->currency_id;
        $this->type = $userAccount->type instanceof UserAccountTypeEnum
            ? $userAccount->type->value
            : (string) $userAccount->type;
        $this->account_number = $userAccount->account_number;
        $this->account_owner = $userAccount->account_owner ?? '';
        $this->status = $userAccount->status;
        $this->is_active = $userAccount->is_active;
    }

    public function rules(): array
    {
        return [
            'currency_id' => [
                'required',
                'integer',
                Rule::exists('currencies', 'id')->where(function ($query) {
                    $query->where(function ($query) {
                        $query->whereNull('deleted_at')->where('is_active', true);
                    })->when($this->userAccount?->currency_id, function ($query) {
                        $query->orWhere('id', $this->userAccount->currency_id);
                    });
                }),
            ],
            'type' => ['required', 'string', Rule::enum(UserAccountTypeEnum::class)],
            'account_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user_accounts', 'account_number')
                    ->where(fn ($query) => $query->where('user_id', $this->user?->id)->whereNull('deleted_at'))
                    ->ignore($this->userAccount?->id),
            ],
            'account_owner' => ['nullable', 'string', 'max:255'],
            'status' => [
                'required',
                'string',
                Rule::in([
                    UserAccount::STATUS_PENDING,
                    UserAccount::STATUS_APPROVED,
                    UserAccount::STATUS_REJECTED,
                ]),
            ],
            'is_active' => ['boolean'],
        ];
    }

    public function store(): UserAccount
    {
        $this->validate();

        return UserAccount::query()->create([
            'user_id' => $this->user->id,
            'currency_id' => (int) $this->currency_id,
            'type' => $this->type,
            'account_number' => $this->account_number,
            'account_owner' => filled($this->account_owner) ? $this->account_owner : null,
            'status' => $this->status,
            'is_active' => $this->is_active,
        ])->load('currency');
    }

    public function update(): void
    {
        $this->validate();

        $this->userAccount->update([
            'currency_id' => (int) $this->currency_id,
            'type' => $this->type,
            'account_number' => $this->account_number,
            'account_owner' => filled($this->account_owner) ? $this->account_owner : null,
            'status' => $this->status,
            'is_active' => $this->is_active,
        ]);

        $this->setModel($this->userAccount->fresh(['currency', 'user']));
    }

    /**
     * @return Collection<int, Currency>
     */
    public function availableCurrencies(?string $search = null): Collection
    {
        $currencies = Currency::query()
            ->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('symbol', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        if (blank($search) && filled($this->currency_id)) {
            $selected = Currency::query()
                ->withTrashed()
                ->whereKey($this->currency_id)
                ->whereNotIn('id', $currencies->pluck('id'))
                ->get();

            $currencies = $selected->merge($currencies);
        }

        return $currencies;
    }
}
