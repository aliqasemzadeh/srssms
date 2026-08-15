<?php

use App\Models\Phonebook\Contact;
use App\Models\Phonebook\Group;
use App\Models\Sms\Gateway;
use App\Services\Sms\SmsBillingService;
use App\Services\Sms\SmsMessageInspector;
use App\Services\Sms\SmsPartCounter;
use App\Support\MobileNumber;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Tags\Tag;

new class extends Component
{
    private const URL_MIRROR_MAX_LENGTH = 2000;

    private const GROUP_CONTACT_PAGE_SIZE = 50;

    #[Url]
    public string $contacts = '';

    #[Url(as: 'groups')]
    public string $groupsQuery = '';

    #[Url(as: 'tags')]
    public string $tagsQuery = '';

    public ?int $gateway_id = null;

    public string $body = '';

    public string $search = '';

    /** @var array<int, int> */
    public array $contact_ids = [];

    /** @var array<int, int> */
    public array $group_ids = [];

    /** @var array<int, int> */
    public array $tag_ids = [];

    /** @var array<int, int> */
    public array $explicit_contact_ids = [];

    /** @var array<int, string> */
    public array $manual_mobiles = [];

    /** @var array<int|string, int> */
    public array $groupContactLimits = [];

    public function mount(): void
    {
        $urlContacts = $this->parseIdList($this->contacts);
        $urlGroups = $this->parseIdList($this->groupsQuery);
        $urlTags = $this->parseIdList($this->tagsQuery);
        $hasUrlSelection = $urlContacts !== [] || $urlGroups !== [] || $urlTags !== [];

        $compose = session('sms_compose_state');
        $compose = is_array($compose) ? $compose : [];

        if ($hasUrlSelection) {
            if ($urlContacts !== []) {
                $this->contact_ids = Contact::query()
                    ->ownedBy(Auth::user())
                    ->whereIn('id', $urlContacts)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $this->explicit_contact_ids = $this->contact_ids;
            }

            if ($urlGroups !== []) {
                $this->group_ids = Group::query()
                    ->where('user_id', Auth::id())
                    ->whereIn('id', $urlGroups)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                foreach ($this->group_ids as $groupId) {
                    $this->mergeContactIds($this->contactIdsForGroup((int) $groupId));
                }
            }

            if ($urlTags !== []) {
                $this->tag_ids = Tag::query()
                    ->where('type', Contact::tagTypeFor(Auth::user()))
                    ->whereIn('id', $urlTags)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                foreach ($this->tag_ids as $tagId) {
                    $this->mergeContactIds($this->contactIdsForTag((int) $tagId));
                }
            }
        } else {
            $this->contact_ids = $this->sanitizeOwnedContactIds($compose['contact_ids'] ?? []);
            $this->group_ids = $this->sanitizeOwnedGroupIds($compose['group_ids'] ?? []);
            $this->tag_ids = $this->sanitizeOwnedTagIds($compose['tag_ids'] ?? []);
            $this->explicit_contact_ids = $this->sanitizeOwnedContactIds($compose['explicit_contact_ids'] ?? []);
        }

        if (array_key_exists('body', $compose)) {
            $this->body = (string) $compose['body'];
        }

        $this->manual_mobiles = collect($compose['manual_mobiles'] ?? [])
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->filter(fn ($mobile) => MobileNumber::isValid($mobile))
            ->unique()
            ->values()
            ->all();

        if (! empty($compose['gateway_id'])) {
            $gateway = Gateway::query()
                ->usableBy(Auth::user())
                ->where('is_active', true)
                ->find((int) $compose['gateway_id']);

            $this->gateway_id = $gateway?->id;
        }

        if (! $this->gateway_id) {
            $defaultGateway = Gateway::query()
                ->usableBy(Auth::user())
                ->where('is_active', true)
                ->orderBy('id')
                ->first();

            $this->gateway_id = $defaultGateway?->id;
        }

        foreach ($this->group_ids as $groupId) {
            $this->groupContactLimits[(int) $groupId] ??= self::GROUP_CONTACT_PAGE_SIZE;
        }

        $this->syncUrlMirrors();
        $this->persistComposeState();
    }

    public function updatedBody(): void
    {
        $this->persistComposeState();
    }

    public function updatedGatewayId(): void
    {
        $this->persistComposeState();
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
    public function groupOptions(): Collection
    {
        $search = trim($this->search);

        return Group::query()
            ->where('user_id', Auth::id())
            ->withCount('contacts')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('contacts', function ($contactQuery) use ($search) {
                            $contactQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function tagOptions(): Collection
    {
        $search = trim($this->search);
        $locale = app()->getLocale();

        return Tag::query()
            ->where('type', Contact::tagTypeFor(Auth::user()))
            ->when($search !== '', fn ($query) => $query->where("name->{$locale}", 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function expandedGroupContacts(): Collection
    {
        if ($this->group_ids === []) {
            return collect();
        }

        $search = trim($this->search);

        return Contact::query()
            ->ownedBy(Auth::user())
            ->whereHas('groups', fn ($q) => $q->whereIn('phonebook_groups.id', $this->group_ids))
            ->with(['groups' => fn ($q) => $q->whereIn('phonebook_groups.id', $this->group_ids)->select('phonebook_groups.id')])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'mobile']);
    }

    /**
     * All member IDs for open groups (ignores search) for accurate selection counts.
     *
     * @return array<int, array<int, int>>
     */
    #[Computed]
    public function openGroupMemberIds(): array
    {
        if ($this->group_ids === []) {
            return [];
        }

        $map = [];
        foreach ($this->group_ids as $groupId) {
            $map[(int) $groupId] = [];
        }

        $contacts = Contact::query()
            ->ownedBy(Auth::user())
            ->whereHas('groups', fn ($q) => $q->whereIn('phonebook_groups.id', $this->group_ids))
            ->with(['groups' => fn ($q) => $q->whereIn('phonebook_groups.id', $this->group_ids)->select('phonebook_groups.id')])
            ->get(['id']);

        foreach ($contacts as $contact) {
            foreach ($contact->groups as $group) {
                $map[(int) $group->id][] = (int) $contact->id;
            }
        }

        return $map;
    }

    #[Computed]
    public function resolvedContacts(): Collection
    {
        $ids = collect($this->contact_ids)->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Contact::query()
            ->ownedBy(Auth::user())
            ->whereIn('id', $ids)
            ->get(['id', 'first_name', 'last_name', 'mobile']);
    }

    #[Computed]
    public function recipientCount(): int
    {
        $contactMobiles = $this->resolvedContacts
            ->pluck('mobile')
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->all();

        $manual = collect($this->manual_mobiles)
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->reject(fn ($mobile) => in_array($mobile, $contactMobiles, true))
            ->unique()
            ->count();

        return $this->resolvedContacts->count() + $manual;
    }

    #[Computed]
    public function estimate(): ?array
    {
        if (! $this->gateway_id || blank($this->body) || $this->recipientCount === 0) {
            return null;
        }

        $gateway = Gateway::query()->usableBy(Auth::user())->find($this->gateway_id);

        if (! $gateway) {
            return null;
        }

        return app(SmsBillingService::class)->estimate(
            $gateway,
            $this->body,
            $this->recipientCount
        );
    }

    public function toggleGroup(int $groupId): void
    {
        $groupId = (int) $groupId;

        if (! Group::query()->where('user_id', Auth::id())->whereKey($groupId)->exists()) {
            return;
        }

        if (in_array($groupId, $this->group_ids, true)) {
            $this->group_ids = array_values(array_filter(
                $this->group_ids,
                fn ($id) => (int) $id !== $groupId
            ));

            $this->pruneContactIds($this->contactIdsForGroup($groupId));
            unset($this->groupContactLimits[$groupId]);
        } else {
            $this->group_ids[] = $groupId;
            $this->group_ids = array_values(array_unique(array_map('intval', $this->group_ids)));
            $this->groupContactLimits[$groupId] = self::GROUP_CONTACT_PAGE_SIZE;
            $this->mergeContactIds($this->contactIdsForGroup($groupId));
        }

        unset($this->expandedGroupContacts, $this->openGroupMemberIds, $this->resolvedContacts, $this->recipientCount, $this->estimate);
        $this->afterSelectionChange();
    }

    public function selectAllInGroup(int $groupId): void
    {
        if (! in_array($groupId, $this->group_ids, true)) {
            return;
        }

        $this->mergeContactIds($this->contactIdsForGroup($groupId));
        unset($this->resolvedContacts, $this->recipientCount, $this->estimate, $this->openGroupMemberIds);
        $this->afterSelectionChange();
    }

    public function deselectAllInGroup(int $groupId): void
    {
        if (! in_array($groupId, $this->group_ids, true)) {
            return;
        }

        $memberIds = $this->contactIdsForGroup($groupId);
        $this->contact_ids = array_values(array_diff($this->contact_ids, $memberIds));
        $this->explicit_contact_ids = array_values(array_diff($this->explicit_contact_ids, $memberIds));

        unset($this->resolvedContacts, $this->recipientCount, $this->estimate, $this->openGroupMemberIds);
        $this->afterSelectionChange();
    }

    public function toggleContact(int $contactId): void
    {
        $contactId = (int) $contactId;

        if (! Contact::query()->ownedBy(Auth::user())->whereKey($contactId)->exists()) {
            return;
        }

        if (in_array($contactId, $this->contact_ids, true)) {
            $this->contact_ids = array_values(array_filter(
                $this->contact_ids,
                fn ($id) => (int) $id !== $contactId
            ));
            $this->explicit_contact_ids = array_values(array_filter(
                $this->explicit_contact_ids,
                fn ($id) => (int) $id !== $contactId
            ));
        } else {
            $this->contact_ids[] = $contactId;
            $this->explicit_contact_ids[] = $contactId;
            $this->contact_ids = array_values(array_unique(array_map('intval', $this->contact_ids)));
            $this->explicit_contact_ids = array_values(array_unique(array_map('intval', $this->explicit_contact_ids)));
        }

        unset($this->resolvedContacts, $this->recipientCount, $this->estimate);
        $this->afterSelectionChange();
    }

    public function toggleTag(int $tagId): void
    {
        $tagId = (int) $tagId;

        $exists = Tag::query()
            ->where('type', Contact::tagTypeFor(Auth::user()))
            ->whereKey($tagId)
            ->exists();

        if (! $exists) {
            return;
        }

        if (in_array($tagId, $this->tag_ids, true)) {
            $this->tag_ids = array_values(array_filter(
                $this->tag_ids,
                fn ($id) => (int) $id !== $tagId
            ));
            $this->pruneContactIds($this->contactIdsForTag($tagId));
        } else {
            $this->tag_ids[] = $tagId;
            $this->tag_ids = array_values(array_unique(array_map('intval', $this->tag_ids)));
            $this->mergeContactIds($this->contactIdsForTag($tagId));
        }

        unset($this->resolvedContacts, $this->recipientCount, $this->estimate);
        $this->afterSelectionChange();
    }

    public function loadMoreGroupContacts(int $groupId): void
    {
        if (! in_array($groupId, $this->group_ids, true)) {
            return;
        }

        $current = (int) ($this->groupContactLimits[$groupId] ?? self::GROUP_CONTACT_PAGE_SIZE);
        $this->groupContactLimits[$groupId] = $current + self::GROUP_CONTACT_PAGE_SIZE;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    public function addManualMobiles(array $tokens): void
    {
        $contactMobiles = $this->resolvedContacts
            ->pluck('mobile')
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->all();

        $added = false;

        foreach ($tokens as $token) {
            $normalized = MobileNumber::normalize((string) $token);

            if ($normalized === '' || ! MobileNumber::isValid($normalized)) {
                continue;
            }

            if (in_array($normalized, $this->manual_mobiles, true) || in_array($normalized, $contactMobiles, true)) {
                continue;
            }

            $this->manual_mobiles[] = $normalized;
            $added = true;
        }

        if ($added) {
            $this->manual_mobiles = array_values(array_unique($this->manual_mobiles));
            unset($this->recipientCount, $this->estimate);
            $this->persistComposeState();
        }
    }

    public function removeManualMobile(string $mobile): void
    {
        $normalized = MobileNumber::normalize($mobile);
        $this->manual_mobiles = array_values(array_filter(
            $this->manual_mobiles,
            fn ($item) => MobileNumber::normalize((string) $item) !== $normalized
        ));

        unset($this->recipientCount, $this->estimate);
        $this->persistComposeState();
    }

    public function clearSelection(): void
    {
        $this->contact_ids = [];
        $this->group_ids = [];
        $this->tag_ids = [];
        $this->explicit_contact_ids = [];
        $this->manual_mobiles = [];
        $this->groupContactLimits = [];

        unset($this->expandedGroupContacts, $this->openGroupMemberIds, $this->resolvedContacts, $this->recipientCount, $this->estimate);
        $this->afterSelectionChange();
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

        if (! app(SmsMessageInspector::class)->containsOptOut($this->body)) {
            throw ValidationException::withMessages([
                'body' => __('general.sms_opt_out_required'),
            ]);
        }

        if ($this->recipientCount === 0) {
            Flux::toast(__('general.no_sms_recipients'));

            return null;
        }

        $contactMobiles = $this->resolvedContacts
            ->pluck('mobile')
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->all();

        $manualMobiles = collect($this->manual_mobiles)
            ->map(fn ($mobile) => MobileNumber::normalize((string) $mobile))
            ->filter(fn ($mobile) => MobileNumber::isValid($mobile))
            ->reject(fn ($mobile) => in_array($mobile, $contactMobiles, true))
            ->unique()
            ->values()
            ->all();

        $this->persistComposeState();

        session([
            'sms_draft' => [
                'gateway_id' => $this->gateway_id,
                'body' => $this->body,
                'contact_ids' => $this->resolvedContacts->pluck('id')->map(fn ($id) => (int) $id)->all(),
                'manual_mobiles' => $manualMobiles,
            ],
        ]);

        return $this->redirect(route('panels.user.sms.preview'), navigate: true);
    }

    /**
     * @return array<int, int>
     */
    protected function contactIdsForGroup(int $groupId): array
    {
        return Contact::query()
            ->ownedBy(Auth::user())
            ->whereHas('groups', fn ($q) => $q->where('phonebook_groups.id', $groupId))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function contactIdsForTag(int $tagId): array
    {
        $tag = Tag::query()
            ->where('type', Contact::tagTypeFor(Auth::user()))
            ->whereKey($tagId)
            ->first();

        if (! $tag) {
            return [];
        }

        return Contact::query()
            ->ownedBy(Auth::user())
            ->withAnyTags([$tag], Contact::tagTypeFor(Auth::user()))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  array<int, int>  $ids
     */
    protected function mergeContactIds(array $ids): void
    {
        $this->contact_ids = array_values(array_unique(array_merge(
            array_map('intval', $this->contact_ids),
            array_map('intval', $ids)
        )));
    }

    /**
     * @param  array<int, int>  $candidateIds
     */
    protected function pruneContactIds(array $candidateIds): void
    {
        $stillCovered = $this->coveredContactIds();
        $explicit = array_map('intval', $this->explicit_contact_ids);

        $remove = collect($candidateIds)
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => in_array($id, $stillCovered, true) || in_array($id, $explicit, true))
            ->all();

        $this->contact_ids = array_values(array_diff(array_map('intval', $this->contact_ids), $remove));
    }

    /**
     * @return array<int, int>
     */
    protected function coveredContactIds(): array
    {
        $ids = collect();

        foreach ($this->group_ids as $groupId) {
            $ids = $ids->merge($this->contactIdsForGroup((int) $groupId));
        }

        foreach ($this->tag_ids as $tagId) {
            $ids = $ids->merge($this->contactIdsForTag((int) $tagId));
        }

        return $ids->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    protected function afterSelectionChange(): void
    {
        $this->syncUrlMirrors();
        $this->persistComposeState();
    }

    protected function syncUrlMirrors(): void
    {
        $contacts = implode(',', array_map('intval', $this->contact_ids));
        $groups = implode(',', array_map('intval', $this->group_ids));
        $tags = implode(',', array_map('intval', $this->tag_ids));

        if (strlen($contacts) <= self::URL_MIRROR_MAX_LENGTH) {
            $this->contacts = $contacts;
        }

        if (strlen($groups) <= self::URL_MIRROR_MAX_LENGTH) {
            $this->groupsQuery = $groups;
        }

        if (strlen($tags) <= self::URL_MIRROR_MAX_LENGTH) {
            $this->tagsQuery = $tags;
        }
    }

    protected function persistComposeState(): void
    {
        session([
            'sms_compose_state' => [
                'gateway_id' => $this->gateway_id,
                'body' => $this->body,
                'contact_ids' => array_values(array_map('intval', $this->contact_ids)),
                'group_ids' => array_values(array_map('intval', $this->group_ids)),
                'tag_ids' => array_values(array_map('intval', $this->tag_ids)),
                'explicit_contact_ids' => array_values(array_map('intval', $this->explicit_contact_ids)),
                'manual_mobiles' => array_values($this->manual_mobiles),
            ],
        ]);
    }

    /**
     * @return array<int, int>
     */
    protected function parseIdList(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function sanitizeOwnedContactIds(array $ids): array
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        if ($ids === []) {
            return [];
        }

        return Contact::query()
            ->ownedBy(Auth::user())
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function sanitizeOwnedGroupIds(array $ids): array
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        if ($ids === []) {
            return [];
        }

        return Group::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function sanitizeOwnedTagIds(array $ids): array
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        if ($ids === []) {
            return [];
        }

        return Tag::query()
            ->where('type', Contact::tagTypeFor(Auth::user()))
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return Collection<int, Contact>
     */
    public function contactsForGroup(int $groupId): Collection
    {
        $contacts = $this->expandedGroupContacts
            ->filter(fn (Contact $contact) => $contact->groups->contains('id', $groupId))
            ->values();

        if (trim($this->search) !== '') {
            return $contacts;
        }

        $limit = (int) ($this->groupContactLimits[$groupId] ?? self::GROUP_CONTACT_PAGE_SIZE);

        return $contacts->take($limit)->values();
    }

    public function groupHasMoreContacts(int $groupId, int $totalCount): bool
    {
        $limit = (int) ($this->groupContactLimits[$groupId] ?? self::GROUP_CONTACT_PAGE_SIZE);
        $search = trim($this->search);

        if ($search !== '') {
            return false;
        }

        return $totalCount > $limit;
    }

    public function selectedCountInGroup(int $groupId): int
    {
        $memberIds = $this->openGroupMemberIds[(int) $groupId] ?? [];

        return count(array_intersect(array_map('intval', $this->contact_ids), $memberIds));
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.send_sms') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
            <flux:breadcrumbs.item href="{{ route('panels.user.sms.message.index') }}" wire:navigate>{{ __('general.sms_messages') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('general.send_sms') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:card class="space-y-6">
            <flux:heading size="lg">{{ __('general.send_sms') }}</flux:heading>

            @if ($this->gateways->isEmpty())
                <flux:callout icon="triangle-alert" color="amber" variant="secondary">
                    <flux:callout.heading>{{ __('general.no_usable_sms_gateway') }}</flux:callout.heading>
                </flux:callout>
            @endif

            <flux:callout icon="info" color="sky" variant="secondary">
                <flux:callout.heading>{{ __('general.sms_opt_out_hint') }}</flux:callout.heading>
            </flux:callout>

            <flux:select wire:model.live="gateway_id" variant="listbox" searchable label="{{ __('general.sms_gateway') }}" placeholder="{{ __('general.select_sms_gateway') }}">
                @foreach ($this->gateways as $gateway)
                    <flux:select.option value="{{ $gateway->id }}">
                        {{ $gateway->title }} — {{ number_format($gateway->sms_rate) }} {{ __('general.rial') }}/{{ __('general.part') }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model.live.debounce.300ms="body" label="{{ __('general.message_body') }}" rows="5" />

            @php
                $inspector = app(SmsMessageInspector::class);
                $analysis = app(SmsPartCounter::class)->analyze($body ?: ' ');
                $isEnglish = filled($body) && $inspector->isEnglish($body);
            @endphp
            @if ($analysis)
                <div class="flex flex-wrap gap-2 text-sm text-zinc-500">
                    <flux:badge size="sm" color="zinc">{{ __('general.encoding') }}: {{ $analysis['encoding']->label() }}</flux:badge>
                    <flux:badge size="sm" color="zinc">{{ __('general.parts_count') }}: {{ blank($body) ? 0 : $analysis['parts_count'] }}</flux:badge>
                    <flux:badge size="sm" color="zinc">{{ __('general.length') }}: {{ blank($body) ? 0 : $analysis['length'] }}</flux:badge>
                    @if ($isEnglish)
                        <flux:badge size="sm" color="amber">{{ __('general.english_sms_double_rate') }}</flux:badge>
                    @endif
                    @if (filled($body) && ! $inspector->containsOptOut($body))
                        <flux:badge size="sm" color="red">{{ __('general.sms_opt_out_required') }}</flux:badge>
                    @endif
                </div>
            @endif
        </flux:card>

        <flux:card class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:heading size="lg">{{ __('general.recipients') }}</flux:heading>
                @if ($this->recipientCount > 0)
                    <flux:button size="sm" variant="ghost" wire:click="clearSelection">{{ __('general.clear_selection') }}</flux:button>
                @endif
            </div>

            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="search"
                placeholder="{{ __('general.search_contacts_groups_tags') }}"
                clearable
                class="max-w-md"
            />

            <div class="space-y-3">
                <flux:heading size="sm">{{ __('general.phonebook_groups') }}</flux:heading>

                @forelse ($this->groupOptions as $group)
                    @php
                        $isOpen = in_array($group->id, $group_ids, true);
                        $selectedInGroup = $isOpen ? $this->selectedCountInGroup($group->id) : 0;
                    @endphp
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700" wire:key="sms-group-{{ $group->id }}">
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <button
                                type="button"
                                class="flex min-w-0 flex-1 items-center gap-3 text-start"
                                wire:click="toggleGroup({{ $group->id }})"
                            >
                                <flux:checkbox :checked="$isOpen" />
                                <span class="truncate font-medium">{{ $group->name }}</span>
                            </button>
                            <flux:badge size="sm" color="{{ $isOpen ? 'teal' : 'zinc' }}">
                                @if ($isOpen)
                                    {{ __('general.group_selection_count', ['selected' => $selectedInGroup, 'total' => $group->contacts_count]) }}
                                @else
                                    {{ $group->contacts_count }}
                                @endif
                            </flux:badge>
                        </div>

                        @if ($isOpen)
                            <div class="space-y-2 border-t border-zinc-200 px-3 py-3 dark:border-zinc-700">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button size="xs" variant="ghost" wire:click="selectAllInGroup({{ $group->id }})">
                                        {{ __('general.select_all') }}
                                    </flux:button>
                                    <flux:button size="xs" variant="ghost" wire:click="deselectAllInGroup({{ $group->id }})">
                                        {{ __('general.deselect_all') }}
                                    </flux:button>
                                </div>

                                <div class="max-h-64 space-y-1 overflow-y-auto">
                                    @forelse ($this->contactsForGroup($group->id) as $contact)
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-md px-2 py-1.5 text-start hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            wire:key="sms-group-{{ $group->id }}-contact-{{ $contact->id }}"
                                            wire:click="toggleContact({{ $contact->id }})"
                                        >
                                            <flux:checkbox :checked="in_array($contact->id, $contact_ids, true)" />
                                            <span class="min-w-0 flex-1 truncate text-sm">
                                                {{ $contact->full_name }}
                                                <span class="text-zinc-500" dir="ltr">— {{ $contact->mobile }}</span>
                                            </span>
                                        </button>
                                    @empty
                                        <flux:text size="sm" class="text-zinc-500">{{ __('general.no_contacts_in_group') }}</flux:text>
                                    @endforelse
                                </div>

                                @if ($this->groupHasMoreContacts($group->id, $group->contacts_count))
                                    <flux:button size="xs" variant="ghost" wire:click="loadMoreGroupContacts({{ $group->id }})">
                                        {{ __('general.load_more') }}
                                    </flux:button>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <flux:text size="sm" class="text-zinc-500">—</flux:text>
                @endforelse
            </div>

            @if ($this->tagOptions->isNotEmpty())
                <div class="space-y-3">
                    <flux:heading size="sm">{{ __('general.phonebook_tags') }}</flux:heading>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($this->tagOptions as $tag)
                            <button
                                type="button"
                                class="flex items-center gap-3 rounded-lg border border-zinc-200 px-3 py-2 text-start dark:border-zinc-700"
                                wire:key="sms-tag-{{ $tag->id }}"
                                wire:click="toggleTag({{ $tag->id }})"
                            >
                                <flux:checkbox :checked="in_array($tag->id, $tag_ids, true)" />
                                <span class="truncate text-sm">{{ $tag->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div
                class="space-y-3"
                x-data="{
                    draft: '',
                    commit(flush = false) {
                        if (flush) {
                            const tokens = this.draft.split(/,|\n/).map((part) => part.trim()).filter(Boolean);
                            if (tokens.length) {
                                $wire.addManualMobiles(tokens);
                            }
                            this.draft = '';
                            return;
                        }

                        if (! this.draft.includes(',')) {
                            return;
                        }

                        const parts = this.draft.split(',');
                        const remainder = parts.pop() ?? '';
                        const complete = parts.map((part) => part.trim()).filter(Boolean);

                        if (complete.length) {
                            $wire.addManualMobiles(complete);
                        }

                        this.draft = remainder.replace(/^\s+/, '');
                    }
                }"
            >
                <flux:field>
                    <flux:label>{{ __('general.manual_numbers') }}</flux:label>
                    <flux:description>{{ __('general.manual_numbers_hint') }}</flux:description>
                    <div dir="ltr">
                        <flux:textarea
                            x-model="draft"
                            x-on:keydown.enter.prevent="commit(true)"
                            x-on:input="if (draft.includes(',')) commit(false)"
                            rows="2"
                            class="font-mono"
                            placeholder="0912..., 0935..."
                        />
                    </div>
                </flux:field>

                @if ($manual_mobiles !== [])
                    <div class="flex flex-wrap gap-2" dir="ltr">
                        @foreach ($manual_mobiles as $mobile)
                            <flux:badge color="teal" wire:key="manual-mobile-{{ $mobile }}">
                                <span class="font-mono">{{ $mobile }}</span>
                                <flux:badge.close wire:click="removeManualMobile('{{ $mobile }}')" />
                            </flux:badge>
                        @endforeach
                    </div>
                @endif
            </div>

            <flux:callout icon="users" variant="secondary">
                <flux:callout.heading>
                    {{ __('general.recipients_count', ['count' => $this->recipientCount]) }}
                </flux:callout.heading>
                @if ($this->estimate)
                    <flux:text>
                        {{ __('general.estimated_cost') }}:
                        <strong>{{ number_format($this->estimate['cost']) }}</strong> {{ __('general.rial') }}
                        @if (($this->estimate['billing_multiplier'] ?? 1) > 1)
                            <span class="text-amber-600">(×{{ $this->estimate['billing_multiplier'] }})</span>
                        @endif
                    </flux:text>
                @endif
            </flux:callout>

            <flux:button variant="primary" color="teal" class="w-full" wire:click="goToPreview" icon="eye">
                {{ __('actions.preview') }}
            </flux:button>
        </flux:card>
    </div>
</div>
