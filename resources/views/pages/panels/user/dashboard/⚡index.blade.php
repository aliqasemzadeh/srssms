<?php

use App\Enums\Sms\SmsMessageStatusEnum;
use App\Enums\Support\TicketStatusEnum;
use App\Models\Finance\Currency;
use App\Models\Finance\Wallet;
use App\Models\Phonebook\Contact;
use App\Models\Sms\Gateway;
use App\Models\Sms\Message;
use App\Models\Support\Ticket;
use App\Settings\SmsSettings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function activeLinesCount(): int
    {
        return Gateway::query()
            ->usableBy(Auth::user())
            ->where('is_active', true)
            ->count();
    }

    #[Computed]
    public function todaySentMessagesCount(): int
    {
        return Message::query()
            ->where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->count();
    }

    #[Computed]
    public function totalSentMessagesCount(): int
    {
        return Message::query()
            ->where('user_id', Auth::id())
            ->count();
    }

    /**
     * @return array{amount: float, symbol: string|null, decimals: int, currency_id: int|null}
     */
    #[Computed]
    public function primaryWallet(): array
    {
        try {
            $currencyId = app(SmsSettings::class)->billing_currency_id;
        } catch (\Throwable) {
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
                'currency_id' => null,
            ];
        }

        $wallet = Wallet::query()
            ->where('user_id', Auth::id())
            ->where('currency_id', $currency->id)
            ->where('is_active', true)
            ->first();

        return [
            'amount' => $wallet ? (float) $wallet->balance : 0.0,
            'symbol' => $currency->symbol,
            'decimals' => (int) $currency->decimals,
            'currency_id' => $currency->id,
        ];
    }

    #[Computed]
    public function userWallets(): Collection
    {
        return Currency::query()
            ->where('is_active', true)
            ->with([
                'wallets' => fn ($query) => $query->where('user_id', Auth::id()),
            ])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function activeGateways(): Collection
    {
        return Gateway::query()
            ->with('provider')
            ->usableBy(Auth::user())
            ->where('is_active', true)
            ->orderBy('number')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function contactsCount(): int
    {
        return Contact::query()
            ->ownedBy(Auth::user())
            ->count();
    }

    #[Computed]
    public function openTicketsCount(): int
    {
        return Ticket::query()
            ->ownedBy(Auth::user())
            ->where('status', '!=', TicketStatusEnum::Closed->value)
            ->count();
    }

    #[Computed]
    public function recentMessages(): Collection
    {
        return Message::query()
            ->where('user_id', Auth::id())
            ->with(['gateway'])
            ->withCount('recipients')
            ->latest('id')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function recentTickets(): Collection
    {
        return Ticket::query()
            ->ownedBy(Auth::user())
            ->latest('updated_at')
            ->take(5)
            ->get();
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.dashboard') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        {{-- Breadcrumbs & Top Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item>{{ __('general.dashboard') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <div class="sm:hidden shrink-0">
                <flux:dropdown align="end">
                    <flux:button icon:trailing="chevron-down">{{ __('general.actions') }}</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="send" href="{{ route('panels.user.sms.send') }}" wire:navigate>
                            {{ __('general.send_sms') }}
                        </flux:menu.item>
                        <flux:menu.item icon="plus" href="{{ route('panels.user.wallet.charge') }}" wire:navigate>
                            {{ __('general.charge_wallet') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>

            <div class="hidden items-center gap-2 sm:flex shrink-0">
                <flux:button
                    variant="primary"
                    color="teal"
                    icon="send"
                    href="{{ route('panels.user.sms.send') }}"
                    wire:navigate
                >
                    {{ __('general.send_sms') }}
                </flux:button>
                <flux:button
                    variant="filled"
                    color="zinc"
                    icon="plus"
                    href="{{ route('panels.user.wallet.charge') }}"
                    wire:navigate
                >
                    {{ __('general.charge_wallet') }}
                </flux:button>
            </div>
        </div>

        {{-- Welcome Banner --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-gradient-to-r from-teal-500/10 via-teal-500/5 to-transparent border border-teal-500/20 dark:border-teal-500/30">
            <div class="space-y-1">
                <flux:heading size="xl" class="font-bold">
                    {{ __('general.welcome_back') }}، {{ Auth::user()->full_name ?: Auth::user()->username }} 👋
                </flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('general.user_dashboard_subtitle') }}
                </flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:button
                    size="sm"
                    variant="primary"
                    color="teal"
                    icon="plus"
                    href="{{ route('panels.user.wallet.charge') }}"
                    wire:navigate
                >
                    {{ __('general.charge_wallet') }}
                </flux:button>
            </div>
        </div>

        {{-- 4 Top Metric Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- 1. Active Lines --}}
            <flux:card class="relative overflow-hidden space-y-3">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.active_lines_count') }}</flux:text>
                    <div class="p-2.5 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400">
                        <flux:icon.radio class="size-5" />
                    </div>
                </div>
                <div>
                    <flux:heading size="xl" class="font-bold">{{ number_format($this->activeLinesCount) }}</flux:heading>
                </div>
                <div class="flex items-center justify-between text-xs text-zinc-500 pt-1 border-t border-zinc-100 dark:border-zinc-800">
                    <span>{{ __('general.sms_gateways') }}</span>
                    <a href="{{ route('panels.user.sms.send') }}" class="text-teal-600 dark:text-teal-400 hover:underline inline-flex items-center gap-1" wire:navigate>
                        <span>{{ __('general.send_sms') }}</span>
                        <flux:icon.arrow-left-right class="size-3" />
                    </a>
                </div>
            </flux:card>

            {{-- 2. Today's Sent Messages --}}
            <flux:card class="relative overflow-hidden space-y-3">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.today_sent_messages_count') }}</flux:text>
                    <div class="p-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400">
                        <flux:icon.send class="size-5" />
                    </div>
                </div>
                <div>
                    <flux:heading size="xl" class="font-bold">{{ number_format($this->todaySentMessagesCount) }}</flux:heading>
                </div>
                <div class="flex items-center justify-between text-xs text-zinc-500 pt-1 border-t border-zinc-100 dark:border-zinc-800">
                    <span>{{ __('general.total_sent_messages') }}:</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($this->totalSentMessagesCount) }}</span>
                </div>
            </flux:card>

            {{-- 3. Wallet Balance --}}
            <flux:card class="relative overflow-hidden space-y-3">
                @php $pw = $this->primaryWallet; @endphp
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.wallet_balance') }}</flux:text>
                    <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                        <flux:icon.wallet class="size-5" />
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5" dir="ltr">
                    <flux:heading size="xl" class="font-bold font-mono">
                        {{ number_format($pw['amount'], $pw['decimals']) }}
                    </flux:heading>
                    @if ($pw['symbol'])
                        <span class="text-sm font-sans font-medium text-zinc-500">{{ $pw['symbol'] }}</span>
                    @endif
                </div>
                <div class="flex items-center justify-between text-xs text-zinc-500 pt-1 border-t border-zinc-100 dark:border-zinc-800">
                    <span>{{ __('general.wallet') }}</span>
                    <a href="{{ route('panels.user.wallet.charge', array_filter(['currency' => $pw['currency_id']])) }}" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline inline-flex items-center gap-1" wire:navigate>
                        <span>{{ __('general.charge_wallet') }}</span>
                        <flux:icon.arrow-up-right class="size-3" />
                    </a>
                </div>
            </flux:card>

            {{-- 4. Contacts / Phonebook --}}
            <flux:card class="relative overflow-hidden space-y-3">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('general.contacts_count') }}</flux:text>
                    <div class="p-2.5 rounded-xl bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400">
                        <flux:icon.users class="size-5" />
                    </div>
                </div>
                <div>
                    <flux:heading size="xl" class="font-bold">{{ number_format($this->contactsCount) }}</flux:heading>
                </div>
                <div class="flex items-center justify-between text-xs text-zinc-500 pt-1 border-t border-zinc-100 dark:border-zinc-800">
                    <span>{{ __('general.phonebook') }}</span>
                    <a href="{{ route('panels.user.phonebook.index') }}" class="text-violet-600 dark:text-violet-400 hover:underline inline-flex items-center gap-1" wire:navigate>
                        <span>{{ __('general.view_all') }}</span>
                        <flux:icon.arrow-left-right class="size-3" />
                    </a>
                </div>
            </flux:card>
        </div>

        {{-- Quick Access Section --}}
        <div class="space-y-3">
            <flux:heading size="lg">{{ __('general.quick_access') }}</flux:heading>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <a
                    href="{{ route('panels.user.sms.send') }}"
                    class="group flex items-center gap-3.5 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-teal-500/50 hover:shadow-sm transition-all"
                    wire:navigate
                >
                    <div class="p-3 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 group-hover:scale-105 transition-transform">
                        <flux:icon.send class="size-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                            {{ __('general.send_sms') }}
                        </div>
                        <div class="text-xs text-zinc-500 truncate">
                            {{ __('general.sms_messages') }}
                        </div>
                    </div>
                </a>

                <a
                    href="{{ route('panels.user.wallet.charge') }}"
                    class="group flex items-center gap-3.5 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-emerald-500/50 hover:shadow-sm transition-all"
                    wire:navigate
                >
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition-transform">
                        <flux:icon.credit-card class="size-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                            {{ __('general.charge_wallet') }}
                        </div>
                        <div class="text-xs text-zinc-500 truncate">
                            {{ __('general.charge_wallet_hint') }}
                        </div>
                    </div>
                </a>

                <a
                    href="{{ route('panels.user.phonebook.index') }}"
                    class="group flex items-center gap-3.5 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-violet-500/50 hover:shadow-sm transition-all"
                    wire:navigate
                >
                    <div class="p-3 rounded-xl bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 group-hover:scale-105 transition-transform">
                        <flux:icon.book-user class="size-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                            {{ __('general.phonebook') }}
                        </div>
                        <div class="text-xs text-zinc-500 truncate">
                            {{ __('general.contacts') }} و {{ __('general.phonebook_groups') }}
                        </div>
                    </div>
                </a>

                <a
                    href="{{ route('panels.user.sms.token.index') }}"
                    class="group flex items-center gap-3.5 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-amber-500/50 hover:shadow-sm transition-all"
                    wire:navigate
                >
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 group-hover:scale-105 transition-transform">
                        <flux:icon.key class="size-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                            {{ __('general.api_tokens') }}
                        </div>
                        <div class="text-xs text-zinc-500 truncate">
                            {{ __('general.sms_api_endpoint') }}
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Detailed Data Grids (2 Columns) --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Left Column: Recent Messages & Active Lines --}}
            <div class="space-y-6">
                {{-- Recent Sent Messages --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="md">{{ __('general.sms_messages') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500">{{ __('general.recent_messages') ?? 'آخرین پیام‌های ارسالی' }}</flux:text>
                        </div>
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon:trailing="arrow-left-right"
                            href="{{ route('panels.user.sms.message.index') }}"
                            wire:navigate
                        >
                            {{ __('general.view_all') }}
                        </flux:button>
                    </div>

                    @if ($this->recentMessages->isNotEmpty())
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($this->recentMessages as $msg)
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                    <div class="min-w-0 space-y-1 flex-1">
                                        <div class="flex items-center gap-2">
                                            @if ($msg->gateway)
                                                <span class="font-mono text-xs font-semibold text-zinc-800 dark:text-zinc-200" dir="ltr">
                                                    {{ $msg->gateway->number }}
                                                </span>
                                            @endif
                                            <flux:badge size="sm" color="{{ $msg->status->color() }}">
                                                {{ $msg->status->label() }}
                                            </flux:badge>
                                        </div>
                                        <div class="text-sm text-zinc-700 dark:text-zinc-300 truncate max-w-md">
                                            {{ $msg->body }}
                                        </div>
                                        <div class="text-xs text-zinc-400 flex items-center gap-3">
                                            <span>{{ $msg->recipients_count }} {{ __('general.recipients') }}</span>
                                            <span>•</span>
                                            <span>{{ $msg->created_at->toDynamicFormat('Y/m/d H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <flux:tooltip content="{{ __('general.view') ?? 'مشاهده' }}">
                                            <flux:button
                                                size="xs"
                                                variant="subtle"
                                                icon="arrow-left-right"
                                                href="{{ route('panels.user.sms.message.detail', $msg) }}"
                                                wire:navigate
                                            />
                                        </flux:tooltip>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-2 py-8 text-center">
                            <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                <flux:icon.message-square class="size-6" />
                            </div>
                            <flux:text class="text-sm text-zinc-500">{{ __('general.no_sent_messages_yet') }}</flux:text>
                            <flux:button
                                size="sm"
                                variant="primary"
                                color="teal"
                                icon="send"
                                href="{{ route('panels.user.sms.send') }}"
                                wire:navigate
                                class="mt-2"
                            >
                                {{ __('general.send_sms') }}
                            </flux:button>
                        </div>
                    @endif
                </flux:card>

                {{-- Active Lines / Gateways --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="md">{{ __('general.active_lines') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500">{{ __('general.sms_gateways') }}</flux:text>
                        </div>
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon:trailing="send"
                            href="{{ route('panels.user.sms.send') }}"
                            wire:navigate
                        >
                            {{ __('general.send_sms') }}
                        </flux:button>
                    </div>

                    @if ($this->activeGateways->isNotEmpty())
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($this->activeGateways as $gateway)
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-sm font-bold text-zinc-800 dark:text-zinc-200" dir="ltr">
                                                {{ $gateway->number }}
                                            </span>
                                            <flux:badge size="sm" color="{{ $gateway->is_public ? 'zinc' : 'blue' }}">
                                                {{ $gateway->is_public ? __('general.sms_gateway_access_types.shared') : __('general.sms_gateway_access_types.dedicated') }}
                                            </flux:badge>
                                        </div>
                                        <div class="text-xs text-zinc-500">
                                            {{ $gateway->title ?: $gateway->provider?->name }}
                                            @if ($gateway->sms_rate)
                                                • {{ __('general.sms_rate') }}: {{ number_format($gateway->sms_rate) }} {{ __('general.rial') }}
                                            @endif
                                        </div>
                                    </div>
                                    <flux:button
                                        size="xs"
                                        variant="filled"
                                        color="zinc"
                                        icon="send"
                                        href="{{ route('panels.user.sms.send') }}"
                                        wire:navigate
                                    >
                                        {{ __('general.send_sms') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-2 py-8 text-center">
                            <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                <flux:icon.radio class="size-6" />
                            </div>
                            <flux:text class="text-sm text-zinc-500">{{ __('general.no_active_lines') }}</flux:text>
                        </div>
                    @endif
                </flux:card>
            </div>

            {{-- Right Column: Wallets & Recent Support Tickets --}}
            <div class="space-y-6">
                {{-- Wallets List & Charge --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="md">{{ __('general.wallets') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500">{{ __('general.wallet_balance') }}</flux:text>
                        </div>
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon:trailing="arrow-left-right"
                            href="{{ route('panels.user.wallet.index') }}"
                            wire:navigate
                        >
                            {{ __('general.view_all') }}
                        </flux:button>
                    </div>

                    @if ($this->userWallets->isNotEmpty())
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($this->userWallets as $currency)
                                @php
                                    $wallet = $currency->wallets->first();
                                    $decimals = $currency->decimals ?? 8;
                                    $balance = $wallet ? (float) $wallet->balance : 0.0;
                                @endphp
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        @if ($currency->logo)
                                            <img src="{{ asset('storage/' . $currency->logo) }}" alt="{{ $currency->name }}" class="size-8 rounded-lg object-contain bg-zinc-100 dark:bg-zinc-800 p-1" />
                                        @else
                                            <div class="p-2 rounded-lg bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400">
                                                <flux:icon.wallet class="size-4" />
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-sm text-zinc-800 dark:text-zinc-200">
                                                {{ $currency->name }}
                                            </div>
                                            <div class="font-mono text-xs text-zinc-500" dir="ltr">
                                                {{ number_format($balance, $decimals) }} {{ $currency->symbol }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <flux:button
                                            size="xs"
                                            variant="primary"
                                            color="teal"
                                            icon="plus"
                                            href="{{ route('panels.user.wallet.charge', ['currency' => $currency->id]) }}"
                                            wire:navigate
                                        >
                                            {{ __('general.charge') }}
                                        </flux:button>
                                        @if ($wallet)
                                            <flux:tooltip content="{{ __('general.transactions') }}">
                                                <flux:button
                                                    size="xs"
                                                    variant="ghost"
                                                    icon="arrow-left-right"
                                                    href="{{ route('panels.user.wallet.transaction.index', $wallet) }}"
                                                    wire:navigate
                                                />
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-2 py-8 text-center">
                            <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                <flux:icon.wallet class="size-6" />
                            </div>
                            <flux:text class="text-sm text-zinc-500">{{ __('general.no_results_found') }}</flux:text>
                        </div>
                    @endif
                </flux:card>

                {{-- Recent Support Tickets --}}
                <flux:card class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="md">{{ __('general.my_tickets') }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500">{{ __('general.support_system') }}</flux:text>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon:trailing="arrow-left-right"
                                href="{{ route('panels.user.ticket.index') }}"
                                wire:navigate
                            >
                                {{ __('general.view_all') }}
                            </flux:button>
                            <flux:button
                                size="sm"
                                variant="primary"
                                color="teal"
                                icon="plus"
                                href="{{ route('panels.user.ticket.create') }}"
                                wire:navigate
                            >
                                {{ __('general.create_ticket') }}
                            </flux:button>
                        </div>
                    </div>

                    @if ($this->recentTickets->isNotEmpty())
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($this->recentTickets as $ticket)
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                    <div class="space-y-1 min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                                {{ $ticket->title }}
                                            </span>
                                            <flux:badge size="sm" color="{{ $ticket->status->color() }}">
                                                {{ $ticket->status->label() }}
                                            </flux:badge>
                                        </div>
                                        <div class="text-xs text-zinc-400 flex items-center gap-2">
                                            <span>{{ __('general.last_replied_at') }}: {{ $ticket->updated_at->toDynamicFormat('Y/m/d H:i') }}</span>
                                        </div>
                                    </div>
                                    <flux:button
                                        size="xs"
                                        variant="subtle"
                                        icon="arrow-left-right"
                                        href="{{ route('panels.user.ticket.view', $ticket) }}"
                                        wire:navigate
                                    />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center gap-2 py-8 text-center">
                            <div class="p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                <flux:icon.ticket class="size-6" />
                            </div>
                            <flux:text class="text-sm text-zinc-500">{{ __('general.no_tickets_found') }}</flux:text>
                            <flux:button
                                size="sm"
                                variant="primary"
                                color="teal"
                                icon="plus"
                                href="{{ route('panels.user.ticket.create') }}"
                                wire:navigate
                                class="mt-2"
                            >
                                {{ __('general.create_ticket') }}
                            </flux:button>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>
    </div>
</div>
