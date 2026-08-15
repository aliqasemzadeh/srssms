<?php

use App\Models\Phonebook\Contact;
use App\Models\Sms\Gateway;
use App\Services\Sms\SmsBillingService;
use App\Services\Sms\SmsSender;
use App\Support\MobileNumber;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?int $gateway_id = null;

    public string $body = '';

    /** @var array<int, int> */
    public array $contact_ids = [];

    /** @var array<int, string> */
    public array $manual_mobiles = [];

    public function mount(): void
    {
        $draft = session('sms_draft');

        $contactIds = collect($draft['contact_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->all();
        $manualMobiles = collect($draft['manual_mobiles'] ?? [])
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->filter(fn ($mobile) => MobileNumber::isValid($mobile))
            ->unique()
            ->values()
            ->all();

        if (! is_array($draft) || blank($draft['body'] ?? null) || ($contactIds === [] && $manualMobiles === [])) {
            $this->redirect(route('panels.user.sms.send'), navigate: true);

            return;
        }

        $this->gateway_id = (int) ($draft['gateway_id'] ?? 0);
        $this->body = (string) $draft['body'];
        $this->contact_ids = $contactIds;
        $this->manual_mobiles = $manualMobiles;
    }

    #[Computed]
    public function gateway(): ?Gateway
    {
        if (! $this->gateway_id) {
            return null;
        }

        return Gateway::query()->usableBy(Auth::user())->find($this->gateway_id);
    }

    #[Computed]
    public function recipients(): Collection
    {
        $contacts = Contact::query()
            ->ownedBy(Auth::user())
            ->whereIn('id', $this->contact_ids)
            ->get(['id', 'first_name', 'last_name', 'mobile'])
            ->map(fn (Contact $contact) => (object) [
                'id' => $contact->id,
                'full_name' => $contact->full_name,
                'mobile' => $contact->mobile,
                'contact_id' => $contact->id,
                'is_manual' => false,
            ]);

        $contactMobiles = $contacts
            ->pluck('mobile')
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->all();

        $manual = collect($this->manual_mobiles)
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->filter(fn ($mobile) => MobileNumber::isValid($mobile))
            ->reject(fn ($mobile) => in_array($mobile, $contactMobiles, true))
            ->unique()
            ->values()
            ->map(fn (string $mobile, int $index) => (object) [
                'id' => 'manual-'.$index.'-'.$mobile,
                'full_name' => __('general.manual_recipient_badge'),
                'mobile' => $mobile,
                'contact_id' => null,
                'is_manual' => true,
            ]);

        return $contacts->concat($manual)->values();
    }

    #[Computed]
    public function estimate(): ?array
    {
        if (! $this->gateway || $this->recipients->isEmpty()) {
            return null;
        }

        return app(SmsBillingService::class)->estimate(
            $this->gateway,
            $this->body,
            $this->recipients->count()
        );
    }

    #[Computed]
    public function walletBalance(): ?string
    {
        try {
            return app(SmsBillingService::class)->resolveWallet(Auth::user())->available_balance;
        } catch (\Throwable) {
            return null;
        }
    }

    #[Computed]
    public function hasSufficientBalance(): bool
    {
        if (! $this->estimate) {
            return false;
        }

        try {
            app(SmsBillingService::class)->assertSufficientBalance(Auth::user(), $this->estimate['cost']);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function confirmSend(): mixed
    {
        if (! $this->gateway || $this->recipients->isEmpty() || ! $this->estimate) {
            Flux::toast(__('general.no_sms_recipients'));

            return null;
        }

        if (! $this->hasSufficientBalance) {
            Flux::toast(__('general.insufficient_wallet_balance'));

            return null;
        }

        try {
            $payload = $this->recipients->map(fn ($recipient) => [
                'mobile' => $recipient->mobile,
                'contact_id' => $recipient->contact_id,
            ])->all();

            app(SmsSender::class)->queueCampaign(
                $this->gateway,
                Auth::user(),
                $this->body,
                $payload,
                bill: true,
            );
        } catch (\RuntimeException $e) {
            Flux::toast($e->getMessage());

            return null;
        }

        session()->forget(['sms_draft', 'sms_compose_state']);

        Flux::toast(__('general.sms_queued_successfully'));

        return $this->redirect(route('panels.user.sms.message.index'), navigate: true);
    }
};
?>

<div>
    <x-slot name="title">{{ __('actions.preview') }} {{ __('general.send_sms') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
            <flux:breadcrumbs.item href="{{ route('panels.user.sms.send') }}" wire:navigate>{{ __('general.send_sms') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('actions.preview') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:card class="space-y-6">
            <flux:heading size="lg">{{ __('actions.preview') }}</flux:heading>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2 text-sm">
                    <div><span class="text-zinc-500">{{ __('general.sms_gateway') }}:</span> {{ $this->gateway?->title ?? '—' }}</div>
                    <div><span class="text-zinc-500">{{ __('general.recipients') }}:</span> {{ $this->recipients->count() }}</div>
                    @if ($this->estimate)
                        <div><span class="text-zinc-500">{{ __('general.parts_count') }}:</span> {{ $this->estimate['parts_count'] }}</div>
                        <div><span class="text-zinc-500">{{ __('general.encoding') }}:</span> {{ $this->estimate['encoding']->label() }}</div>
                        @if ($this->estimate['is_english'] ?? false)
                            <div>
                                <flux:badge size="sm" color="amber">{{ __('general.english_sms_double_rate') }}</flux:badge>
                            </div>
                        @endif
                        <div><span class="text-zinc-500">{{ __('general.sms_rate') }}:</span> {{ number_format($this->estimate['sms_rate']) }} {{ __('general.rial') }}</div>
                        <div><span class="text-zinc-500">{{ __('general.estimated_cost') }}:</span> <strong>{{ number_format($this->estimate['cost']) }}</strong> {{ __('general.rial') }}</div>
                    @endif
                    <div>
                        <span class="text-zinc-500">{{ __('general.wallet_balance') }}:</span>
                        {{ $this->walletBalance !== null ? number_format((float) $this->walletBalance) : '—' }} {{ __('general.rial') }}
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:text class="font-medium">{{ __('general.final_message_preview') }}</flux:text>
                    <div class="relative overflow-hidden rounded-2xl border border-teal-200/80 bg-gradient-to-b from-teal-50 to-white p-4 shadow-sm dark:border-teal-900/50 dark:from-teal-950/40 dark:to-zinc-900">
                        <div class="mb-3 flex items-center gap-2 text-xs text-teal-700 dark:text-teal-300">
                            <flux:icon name="message-square-text" variant="micro" />
                            <span>{{ __('general.sms_message') }}</span>
                        </div>
                        <div class="rounded-xl bg-white/90 p-4 text-sm leading-7 text-zinc-800 shadow-inner whitespace-pre-wrap dark:bg-zinc-900/80 dark:text-zinc-100" dir="auto">{{ $body }}</div>
                    </div>
                </div>
            </div>

            @if (! $this->hasSufficientBalance)
                <flux:callout icon="triangle-alert" color="red" variant="secondary">
                    <flux:callout.heading>{{ __('general.insufficient_wallet_balance') }}</flux:callout.heading>
                    <flux:text>{{ __('general.charge_wallet_to_send_sms') }}</flux:text>
                </flux:callout>
            @endif

            <div class="flex flex-wrap gap-3">
                <flux:button variant="ghost" :href="route('panels.user.sms.send')" wire:navigate>{{ __('general.back') }}</flux:button>
                <flux:button
                    variant="primary"
                    color="teal"
                    class="flex-1"
                    icon="send"
                    wire:click="confirmSend"
                    :disabled="! $this->hasSufficientBalance"
                >
                    {{ __('general.confirm_and_send') }}
                </flux:button>
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="md" class="mb-4">{{ __('general.recipients') }}</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('general.name') }}</flux:table.column>
                    <flux:table.column>{{ __('general.mobile') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->recipients as $recipient)
                        <flux:table.row :key="$recipient->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <span>{{ $recipient->full_name }}</span>
                                    @if ($recipient->is_manual)
                                        <flux:badge size="sm" color="teal">{{ __('general.manual_recipient_badge') }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell><span dir="ltr">{{ $recipient->mobile }}</span></flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
