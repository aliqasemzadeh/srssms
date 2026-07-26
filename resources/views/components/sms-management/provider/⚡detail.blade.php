<?php

use App\Livewire\Concerns\AuthorizesAdministratorPermissions;
use App\Models\Sms\Provider;
use App\Services\Sms\SmsManager;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use AuthorizesAdministratorPermissions;

    public ?Provider $provider = null;

    #[On('panels.administrator.sms-management.provider.detail.assign-data')]
    public function assignData(int $provider): void
    {
        $this->authorizePermission('sms-management.provider.view');

        $this->provider = Provider::query()->findOrFail($provider);

        Flux::modal('sms-management.provider.detail')->show();
    }

    #[Computed]
    public function webhookToken(): string
    {
        if (! $this->provider) {
            return '';
        }

        return (string) $this->provider->credential('webhook_token', '');
    }

    #[Computed]
    public function receiveWebhookUrl(): string
    {
        return $this->webhookUrl('receive');
    }

    #[Computed]
    public function statusWebhookUrl(): string
    {
        return $this->webhookUrl('status');
    }

    protected function webhookUrl(string $type): string
    {
        if (! $this->provider) {
            return '';
        }

        $url = route('sms.webhook', [
            'provider' => $this->provider->driver,
            'type' => $type,
        ], absolute: true);

        $token = $this->webhookToken;

        if ($token !== '') {
            $url .= (str_contains($url, '?') ? '&' : '?').'token='.urlencode($token);
        }

        return $url;
    }
};
?>

@php
    $driverOptions = app(SmsManager::class)->driverOptions();
@endphp

<flux:modal name="sms-management.provider.detail" flyout position="right" class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('general.provider') }}</flux:heading>
        <flux:subheading>{{ __('general.webhook_urls') }}</flux:subheading>
    </div>

    @if ($provider)
        <div class="space-y-4">
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <flux:text class="text-sm opacity-70">{{ __('general.name') }}</flux:text>
                    <div class="font-medium">{{ $provider->name }}</div>
                </div>
                <div>
                    <flux:text class="text-sm opacity-70">{{ __('general.driver') }}</flux:text>
                    <flux:badge size="sm" color="sky">{{ $driverOptions[$provider->driver] ?? $provider->driver }}</flux:badge>
                </div>
                <div>
                    <flux:text class="text-sm opacity-70">{{ __('general.status') }}</flux:text>
                    <flux:badge size="sm" color="{{ $provider->is_active ? 'green' : 'red' }}">
                        {{ $provider->is_active ? __('general.active') : __('general.inactive') }}
                    </flux:badge>
                </div>
            </div>

            <flux:callout icon="info" variant="secondary">
                <flux:callout.text>{{ __('general.webhook_description') }}</flux:callout.text>
            </flux:callout>

            @if ($this->webhookToken !== '')
                <flux:input
                    :value="$this->webhookToken"
                    label="{{ __('general.webhook_token') }}"
                    type="password"
                    readonly
                    copyable
                    viewable
                    input:class="text-left"
                    dir="ltr"
                />
            @endif

            <flux:input
                :value="$this->receiveWebhookUrl"
                label="{{ __('general.webhook_receive_url') }}"
                readonly
                copyable
                input:class="text-left"
                dir="ltr"
            />

            <div class="space-y-2">
                <flux:heading size="sm">{{ __('general.webhook_receive_params') }}</flux:heading>
                <flux:text class="text-sm opacity-70">{{ __('general.webhook_post_json_hint') }}</flux:text>
                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-start font-medium">{{ __('general.parameter') }}</th>
                                <th class="px-3 py-2 text-start font-medium">{{ __('general.title') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs" dir="ltr">from</td>
                                <td class="px-3 py-2">{{ __('general.webhook_param_from') }}</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs" dir="ltr">gateway</td>
                                <td class="px-3 py-2">{{ __('general.webhook_param_gateway') }}</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs" dir="ltr">text</td>
                                <td class="px-3 py-2">{{ __('general.webhook_param_text') }}</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs" dir="ltr">reference_id</td>
                                <td class="px-3 py-2">{{ __('general.webhook_param_reference_id') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <flux:separator variant="subtle" />

            <flux:input
                :value="$this->statusWebhookUrl"
                label="{{ __('general.webhook_status_url') }}"
                readonly
                copyable
                input:class="text-left"
                dir="ltr"
            />

            <div class="space-y-2">
                <flux:heading size="sm">{{ __('general.webhook_status_params') }}</flux:heading>
                <flux:text class="text-sm opacity-70">{{ __('general.webhook_post_json_hint') }}</flux:text>
                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-start font-medium">{{ __('general.parameter') }}</th>
                                <th class="px-3 py-2 text-start font-medium">{{ __('general.title') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs" dir="ltr">reference_id</td>
                                <td class="px-3 py-2">{{ __('general.webhook_param_message_reference_id') }}</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs" dir="ltr">number</td>
                                <td class="px-3 py-2">{{ __('general.webhook_param_number') }}</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs" dir="ltr">status</td>
                                <td class="px-3 py-2">{{ __('general.webhook_param_status') }}</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs" dir="ltr">datetime</td>
                                <td class="px-3 py-2">{{ __('general.webhook_param_datetime') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</flux:modal>
