<?php

use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Group;
use App\Models\Sms\Gateway;
use App\Services\Sms\SmsBillingService;
use App\Services\Sms\SmsPartCounter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Tags\Tag;

new class extends Component
{
    #[Url]
    public string $contacts = '';

    #[Url]
    public string $groups = '';

    public ?int $gateway_id = null;

    public string $body = '';

    /** @var array<int, int|string> */
    public array $contact_ids = [];

    /** @var array<int, int|string> */
    public array $group_ids = [];

    /** @var array<int, int|string> */
    public array $tag_ids = [];

    public function mount(): void
    {
        $fromQuery = collect(explode(',', $this->contacts))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values()
            ->all();

        if ($fromQuery !== []) {
            $this->contact_ids = Contact::query()
                ->ownedBy(Auth::user())
                ->whereIn('id', $fromQuery)
                ->pluck('id')
                ->all();
        }

        $fromGroups = collect(explode(',', $this->groups))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values()
            ->all();

        if ($fromGroups !== []) {
            $this->group_ids = Group::query()
                ->where('user_id', Auth::id())
                ->whereIn('id', $fromGroups)
                ->pluck('id')
                ->all();
        }

        $defaultGateway = Gateway::query()
            ->usableBy(Auth::user())
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        $this->gateway_id = $defaultGateway?->id;
    }

    #[Computed]
    public function gateways(): Collection
    {
        return Gateway::query()
            ->with('provider')
            ->usableBy(Auth::user())
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    #[Computed]
    public function contactOptions(): Collection
    {
        return Contact::query()
            ->ownedBy(Auth::user())
            ->orderBy('first_name')
            ->limit(500)
            ->get(['id', 'first_name', 'last_name', 'mobile']);
    }

    #[Computed]
    public function groups(): Collection
    {
        return Group::query()->where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function tags(): Collection
    {
        return Tag::query()->where('type', Contact::tagTypeFor(Auth::user()))->get();
    }

    #[Computed]
    public function resolvedRecipients(): Collection
    {
        $ids = collect($this->contact_ids)->map(fn ($id) => (int) $id)->filter();

        $fromGroups = collect();
        if ($this->group_ids !== []) {
            $fromGroups = Contact::query()
                ->ownedBy(Auth::user())
                ->whereHas('groups', fn ($q) => $q->whereIn('phonebook_groups.id', $this->group_ids))
                ->pluck('id');
        }

        $fromTags = collect();
        if ($this->tag_ids !== []) {
            $tagModels = Tag::query()
                ->where('type', Contact::tagTypeFor(Auth::user()))
                ->whereIn('id', $this->tag_ids)
                ->get();

            if ($tagModels->isNotEmpty()) {
                $fromTags = Contact::query()
                    ->ownedBy(Auth::user())
                    ->withAnyTags($tagModels, Contact::tagTypeFor(Auth::user()))
                    ->pluck('id');
            }
        }

        $allIds = $ids->merge($fromGroups)->merge($fromTags)->unique()->values();

        return Contact::query()
            ->ownedBy(Auth::user())
            ->whereIn('id', $allIds)
            ->get(['id', 'first_name', 'last_name', 'mobile']);
    }

    #[Computed]
    public function estimate(): ?array
    {
        if (! $this->gateway_id || blank($this->body)) {
            return null;
        }

        $gateway = Gateway::query()->usableBy(Auth::user())->find($this->gateway_id);

        if (! $gateway) {
            return null;
        }

        return app(SmsBillingService::class)->estimate(
            $gateway,
            $this->body,
            $this->resolvedRecipients->count()
        );
    }

    public function goToPreview(): mixed
    {
        if (blank($this->gateway_id) || (int) $this->gateway_id === 0) {
            $this->gateway_id = null;
        }

        $this->validate([
            'gateway_id' => ['required', 'integer', 'exists:sms_gateways,id'],
            'body' => ['required', 'string', 'max:1000'],
        ], [], [
            'gateway_id' => __('general.sms_gateway'),
            'body' => __('general.message_body'),
        ]);

        if ($this->resolvedRecipients->isEmpty()) {
            \Flux\Flux::toast(__('general.no_sms_recipients'));

            return null;
        }

        session([
            'sms_draft' => [
                'gateway_id' => $this->gateway_id,
                'body' => $this->body,
                'contact_ids' => $this->resolvedRecipients->pluck('id')->all(),
            ],
        ]);

        return $this->redirect(route('panels.user.sms.preview'), navigate: true);
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.send_sms') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
            <flux:breadcrumbs.item href="{{ route('panels.user.sms.index') }}" wire:navigate>{{ __('general.sms_messages') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('general.send_sms') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:card class="space-y-6">
            <flux:heading size="lg">{{ __('general.send_sms') }}</flux:heading>

            @if ($this->gateways->isEmpty())
                <flux:callout icon="triangle-alert" color="amber" variant="secondary">
                    <flux:callout.heading>{{ __('general.no_usable_sms_gateway') }}</flux:callout.heading>
                </flux:callout>
            @endif

            <flux:select wire:model.live="gateway_id" variant="listbox" searchable label="{{ __('general.sms_gateway') }}" placeholder="{{ __('general.select_sms_gateway') }}">
                @foreach ($this->gateways as $gateway)
                    <flux:select.option value="{{ $gateway->id }}">
                        {{ $gateway->title }} — {{ number_format($gateway->sms_rate) }} {{ __('general.rial') }}/{{ __('general.part') }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model.live.debounce.300ms="body" label="{{ __('general.message_body') }}" rows="5" />

            @if ($analysis = app(SmsPartCounter::class)->analyze($body ?: ' '))
                <div class="flex flex-wrap gap-2 text-sm text-zinc-500">
                    <flux:badge size="sm" color="zinc">{{ __('general.encoding') }}: {{ $analysis['encoding']->label() }}</flux:badge>
                    <flux:badge size="sm" color="zinc">{{ __('general.parts_count') }}: {{ blank($body) ? 0 : $analysis['parts_count'] }}</flux:badge>
                    <flux:badge size="sm" color="zinc">{{ __('general.length') }}: {{ blank($body) ? 0 : $analysis['length'] }}</flux:badge>
                </div>
            @endif

            <flux:select wire:model.live="contact_ids" variant="listbox" searchable multiple label="{{ __('general.contacts') }}" clearable>
                @foreach ($this->contactOptions as $contact)
                    <flux:select.option value="{{ $contact->id }}">{{ $contact->full_name }} — {{ $contact->mobile }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="group_ids" variant="listbox" searchable multiple label="{{ __('general.phonebook_groups') }}" clearable>
                @foreach ($this->groups as $group)
                    <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="tag_ids" variant="listbox" searchable multiple label="{{ __('general.phonebook_tags') }}" clearable>
                @foreach ($this->tags as $tag)
                    <flux:select.option value="{{ $tag->id }}">{{ $tag->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:callout icon="users" variant="secondary">
                <flux:callout.heading>
                    {{ __('general.recipients_count', ['count' => $this->resolvedRecipients->count()]) }}
                </flux:callout.heading>
                @if ($this->estimate)
                    <flux:text>
                        {{ __('general.estimated_cost') }}:
                        <strong>{{ number_format($this->estimate['cost']) }}</strong> {{ __('general.rial') }}
                    </flux:text>
                @endif
            </flux:callout>

            <flux:button variant="primary" color="teal" class="w-full" wire:click="goToPreview" icon="eye">
                {{ __('actions.preview') }}
            </flux:button>
        </flux:card>
    </div>
</div>
