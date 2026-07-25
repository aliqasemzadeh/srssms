<?php

use App\Enums\DepositStatusEnum;
use App\Models\Finance\Currency;
use App\Models\Finance\Deposit;
use App\Models\Finance\Wallet;
use App\Settings\PaymentSettings;
use App\Support\PaymentGateways;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url]
    public string $currency = '';

    public string $currency_id = '';

    public string $amount = '';

    public string $driver = '';

    public function mount(PaymentSettings $settings): void
    {
        $enabled = PaymentGateways::enabledDrivers();

        if ($this->currency !== '' && $this->currency_id === '') {
            $this->currency_id = $this->currency;
        }

        if ($this->driver === '') {
            $default = $settings->default;
            $this->driver = in_array($default, $enabled, true)
                ? $default
                : ($enabled[0] ?? '');
        }
    }

    #[Computed]
    public function currencies(): Collection
    {
        return Currency::query()
            ->where('is_active', true)
            ->where('type', 'fiat')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function drivers(): array
    {
        return collect(PaymentGateways::enabledDrivers())
            ->mapWithKeys(fn (string $driver): array => [$driver => PaymentGateways::driverLabel($driver)])
            ->all();
    }

    public function submit()
    {
        $enabled = PaymentGateways::enabledDrivers();

        $this->validate([
            'currency_id' => [
                'required',
                'integer',
                Rule::exists('currencies', 'id')->where(
                    fn ($query) => $query->whereNull('deleted_at')->where('is_active', true)->where('type', 'fiat')
                ),
            ],
            'driver' => ['required', 'string', Rule::in($enabled)],
            'amount' => [
                'required',
                'numeric',
                'min:'.PaymentGateways::minChargeAmount($this->driver ?: ($enabled[0] ?? 'zarinpal')),
            ],
        ], [
            'amount.min' => __('general.min_charge_amount', [
                'amount' => number_format(PaymentGateways::minChargeAmount($this->driver ?: ($enabled[0] ?? 'zarinpal'))),
            ]),
        ]);

        $user = Auth::user();
        $currency = Currency::query()
            ->whereKey($this->currency_id)
            ->where('is_active', true)
            ->where('type', 'fiat')
            ->firstOrFail();

        $driver = $this->driver;
        $gatewayAmount = PaymentGateways::toGatewayAmount($this->amount, $driver, $currency->symbol);

        if ($gatewayAmount < PaymentGateways::minChargeAmount($driver)) {
            $this->addError('amount', __('general.min_charge_amount', [
                'amount' => number_format(PaymentGateways::minChargeAmount($driver)),
            ]));

            return null;
        }

        $deposit = DB::transaction(function () use ($user, $currency, $driver, $gatewayAmount) {
            $wallet = Wallet::withTrashed()
                ->where('user_id', $user->id)
                ->where('currency_id', $currency->id)
                ->first();

            if ($wallet?->trashed()) {
                $wallet->restore();
                $wallet->update([
                    'balance' => 0,
                    'locked_balance' => 0,
                    'is_active' => true,
                ]);
            } elseif (! $wallet) {
                $wallet = Wallet::query()->create([
                    'user_id' => $user->id,
                    'currency_id' => $currency->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                    'is_active' => true,
                ]);
            }

            return Deposit::query()->create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'created_by' => $user->id,
                'amount' => $this->amount,
                'fee' => 0,
                'tax' => 0,
                'method' => PaymentGateways::methodForDriver($driver),
                'status' => DepositStatusEnum::Pending,
                'ip_address' => Request::ip(),
                'meta' => [
                    'driver' => $driver,
                    'gateway_amount' => $gatewayAmount,
                ],
            ]);
        });

        return $this->redirect(route('payment.pay', $deposit), navigate: false);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.charge_wallet') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.wallet.index') }}" wire:navigate>{{ __('general.wallets') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.charge_wallet') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button
                class="shrink-0"
                variant="primary"
                color="zinc"
                icon="arrow-right"
                href="{{ route('panels.user.wallet.index') }}"
                wire:navigate
            >
                {{ __('general.wallets') }}
            </flux:button>
        </div>

        <flux:card class="mx-auto max-w-xl space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-500/20">
                    <flux:icon.wallet class="size-5 text-teal-600 dark:text-teal-400" />
                </div>
                <div>
                    <flux:heading size="lg">{{ __('general.charge_wallet') }}</flux:heading>
                    <flux:subheading>{{ __('general.charge_wallet_hint') }}</flux:subheading>
                </div>
            </div>

            @if ($this->drivers === [])
                <flux:callout variant="warning" icon="triangle-alert" inline>
                    <flux:callout.text>{{ __('general.no_enabled_gateways') }}</flux:callout.text>
                </flux:callout>
            @else
                <form wire:submit="submit" class="space-y-6">
                    <flux:select
                        wire:model="currency_id"
                        variant="listbox"
                        searchable
                        label="{{ __('general.currency') }}"
                        placeholder="{{ __('general.currency') }}..."
                    >
                        @foreach ($this->currencies as $currencyOption)
                            <flux:select.option value="{{ $currencyOption->id }}" wire:key="charge-currency-{{ $currencyOption->id }}">
                                <span dir="ltr">{{ $currencyOption->symbol }}</span> — {{ $currencyOption->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model="amount"
                        type="number"
                        min="{{ \App\Support\PaymentGateways::minChargeAmount($driver ?: 'zarinpal') }}"
                        step="1"
                        label="{{ __('general.amount') }}"
                        description="{{ __('general.gateway_amount_hint') }}"
                        placeholder="10000"
                        dir="ltr"
                    />

                    <flux:select
                        wire:model="driver"
                        variant="listbox"
                        searchable
                        label="{{ __('general.select_gateway') }}"
                        placeholder="{{ __('general.select_gateway') }}..."
                    >
                        @foreach ($this->drivers as $driverKey => $driverLabel)
                            <flux:select.option value="{{ $driverKey }}" wire:key="charge-driver-{{ $driverKey }}">
                                {{ $driverLabel }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:button type="submit" variant="primary" color="teal" icon="wallet" class="w-full" wire:loading.attr="disabled">
                        {{ __('general.charge') }}
                    </flux:button>
                </form>
            @endif
        </flux:card>
    </div>
</div>
