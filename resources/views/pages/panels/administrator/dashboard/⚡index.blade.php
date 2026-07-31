<?php

use App\Models\Finance\Currency;
use App\Models\Finance\Wallet;
use App\Models\Sms\Gateway;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Settings\SmsSettings;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

new class extends Component
{
    protected const CACHE_TTL = 60;

    #[Computed]
    public function usersCount(): int
    {
        return Cache::remember(
            'admin.dashboard.users_count',
            self::CACHE_TTL,
            fn () => User::query()->count(),
        );
    }

    #[Computed]
    public function gatewaysCount(): int
    {
        return Cache::remember(
            'admin.dashboard.gateways_count',
            self::CACHE_TTL,
            fn () => Gateway::query()->count(),
        );
    }

    /**
     * @return array{amount: float, symbol: string|null, decimals: int}
     */
    #[Computed]
    public function walletBalance(): array
    {
        return Cache::remember('admin.dashboard.wallet_balance', self::CACHE_TTL, function (): array {
            try {
                $currencyId = app(SmsSettings::class)->billing_currency_id;
            } catch (Throwable) {
                $currencyId = null;
            }

            $currency = $currencyId
                ? Currency::query()->find($currencyId)
                : Currency::query()->where('is_active', true)->oldest('id')->first();

            if (! $currency) {
                return [
                    'amount' => 0.0,
                    'symbol' => null,
                    'decimals' => 0,
                ];
            }

            $sum = Wallet::query()
                ->where('currency_id', $currency->id)
                ->where('is_active', true)
                ->sum('balance');

            return [
                'amount' => (float) $sum,
                'symbol' => $currency->symbol,
                'decimals' => (int) $currency->decimals,
            ];
        });
    }

    #[Computed]
    public function unansweredTicketsCount(): int
    {
        return Cache::remember(
            'admin.dashboard.unanswered_tickets_count',
            self::CACHE_TTL,
            fn () => Ticket::query()->needsAttention()->count(),
        );
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.dashboard') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" wire:navigate />
            <flux:breadcrumbs.item>{{ __('general.dashboard') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.users_count') }}</flux:text>
                <flux:heading size="md">{{ number_format($this->usersCount) }}</flux:heading>
            </flux:card>

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.sms_gateways_count') }}</flux:text>
                <flux:heading size="md">{{ number_format($this->gatewaysCount) }}</flux:heading>
            </flux:card>

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.total_wallet_balance') }}</flux:text>
                @php $wb = $this->walletBalance; @endphp
                <flux:heading size="md" dir="ltr">
                    {{ number_format($wb['amount'], $wb['decimals']) }}
                    @if ($wb['symbol'])
                        {{ $wb['symbol'] }}
                    @endif
                </flux:heading>
            </flux:card>

            <flux:card class="space-y-1">
                <flux:text class="text-sm text-zinc-500">{{ __('general.unanswered_tickets') }}</flux:text>
                <flux:heading size="md">{{ number_format($this->unansweredTicketsCount) }}</flux:heading>
            </flux:card>
        </div>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('general.quick_access') }}</flux:heading>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @can('user-management.user.view')
                    <flux:button
                        variant="primary"
                        color="blue"
                        icon="users"
                        class="w-full justify-start"
                        href="{{ route('panels.administrator.user-management.user.index') }}"
                        wire:navigate
                    >
                        {{ __('general.users') }}
                    </flux:button>
                @endcan

                @can('sms-management.gateway.view')
                    <flux:button
                        variant="primary"
                        color="cyan"
                        icon="radio-tower"
                        class="w-full justify-start"
                        href="{{ route('panels.administrator.sms-management.gateway.index') }}"
                        wire:navigate
                    >
                        {{ __('general.sms_gateways') }}
                    </flux:button>
                @endcan

                @can('finance-management.wallet.view')
                    <flux:button
                        variant="primary"
                        color="emerald"
                        icon="wallet"
                        class="w-full justify-start"
                        href="{{ route('panels.administrator.finance-management.wallet.index') }}"
                        wire:navigate
                    >
                        {{ __('general.wallets') }}
                    </flux:button>
                @endcan

                @can('support-system.ticket.view')
                    <flux:button
                        variant="primary"
                        color="amber"
                        icon="life-buoy"
                        class="w-full justify-start"
                        href="{{ route('panels.administrator.support-system.ticket.new') }}"
                        wire:navigate
                    >
                        {{ __('general.new_tickets') }}
                    </flux:button>
                @endcan

                @can('finance-management.deposit.view')
                    <flux:button
                        variant="primary"
                        color="teal"
                        icon="banknote"
                        class="w-full justify-start"
                        href="{{ route('panels.administrator.finance-management.deposit.index') }}"
                        wire:navigate
                    >
                        {{ __('general.deposits') }}
                    </flux:button>
                @endcan

                @can('sms-management.message.view')
                    <flux:button
                        variant="primary"
                        color="indigo"
                        icon="message-square-text"
                        class="w-full justify-start"
                        href="{{ route('panels.administrator.sms-management.message.index') }}"
                        wire:navigate
                    >
                        {{ __('general.sms_messages') }}
                    </flux:button>
                @endcan

                @can('system-management.setting.view')
                    <flux:button
                        variant="primary"
                        color="zinc"
                        icon="settings"
                        class="w-full justify-start"
                        href="{{ route('panels.administrator.system-management.setting.index') }}"
                        wire:navigate
                    >
                        {{ __('general.settings') }}
                    </flux:button>
                @endcan
            </div>
        </flux:card>
    </div>
</div>
