<?php

use Livewire\Component;

new class extends Component
{
    /**
     * @return list<array{code: string, http: int}>
     */
    public function errorCodes(): array
    {
        return [
            ['code' => 'queued', 'http' => 200],
            ['code' => 'invalid_token', 'http' => 401],
            ['code' => 'ip_not_allowed', 'http' => 403],
            ['code' => 'validation_error', 'http' => 422],
            ['code' => 'gateway_not_found', 'http' => 404],
            ['code' => 'no_recipients', 'http' => 422],
            ['code' => 'no_wallet', 'http' => 402],
            ['code' => 'insufficient_balance', 'http' => 402],
            ['code' => 'gateway_inactive', 'http' => 403],
            ['code' => 'server_error', 'http' => 500],
        ];
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.sms_api_docs') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.user.dashboard.index') }}" icon="home" wire:navigate />
                <flux:breadcrumbs.item href="{{ route('panels.user.sms.token.index') }}" wire:navigate>{{ __('general.sms_tokens') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.sms_api_docs') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:button variant="primary" color="zinc" icon="arrow-right" :href="route('panels.user.sms.token.index')" wire:navigate>
                {{ __('general.sms_tokens') }}
            </flux:button>
        </div>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('general.sms_api_endpoint') }}</flux:heading>
            <flux:text>{{ __('general.sms_api_endpoint_hint') }}</flux:text>
            <div class="rounded-lg border border-zinc-200 p-4 font-mono text-sm dark:border-zinc-700" dir="ltr">
                GET|POST {{ url('/api/sms/send') }}
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('general.sms_api_auth') }}</flux:heading>
            <flux:text>{{ __('general.sms_api_auth_hint') }}</flux:text>
            <ul class="list-disc space-y-1 pe-5 text-sm">
                <li dir="ltr"><code>Authorization: Bearer {token}</code></li>
                <li dir="ltr"><code>X-Sms-Token: {token}</code></li>
                <li dir="ltr"><code>token={token}</code> (query/body)</li>
            </ul>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('general.sms_api_params') }}</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('general.parameter') }}</flux:table.column>
                    <flux:table.column>{{ __('general.required') }}</flux:table.column>
                    <flux:table.column>{{ __('general.description') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    <flux:table.row>
                        <flux:table.cell><span dir="ltr">token</span></flux:table.cell>
                        <flux:table.cell>{{ __('general.yes_if_no_header') }}</flux:table.cell>
                        <flux:table.cell>{{ __('general.sms_api_param_token') }}</flux:table.cell>
                    </flux:table.row>
                    <flux:table.row>
                        <flux:table.cell><span dir="ltr">to</span></flux:table.cell>
                        <flux:table.cell>{{ __('general.yes') }}</flux:table.cell>
                        <flux:table.cell>{{ __('general.sms_api_param_to') }}</flux:table.cell>
                    </flux:table.row>
                    <flux:table.row>
                        <flux:table.cell><span dir="ltr">message</span></flux:table.cell>
                        <flux:table.cell>{{ __('general.yes') }}</flux:table.cell>
                        <flux:table.cell>{{ __('general.sms_api_param_message') }}</flux:table.cell>
                    </flux:table.row>
                    <flux:table.row>
                        <flux:table.cell><span dir="ltr">gateway</span></flux:table.cell>
                        <flux:table.cell>{{ __('general.yes') }}</flux:table.cell>
                        <flux:table.cell>{{ __('general.sms_api_param_gateway') }}</flux:table.cell>
                    </flux:table.row>
                </flux:table.rows>
            </flux:table>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('general.sms_api_examples') }}</flux:heading>
            <div>
                <flux:text class="mb-2 text-sm opacity-70">GET</flux:text>
                <pre class="overflow-x-auto rounded-lg bg-zinc-100 p-4 text-xs dark:bg-zinc-800" dir="ltr">{{ url('/api/sms/send') }}?token=YOUR_TOKEN&to=09120000000&message=Hello&gateway=1000</pre>
            </div>
            <div>
                <flux:text class="mb-2 text-sm opacity-70">POST (JSON)</flux:text>
                <pre class="overflow-x-auto rounded-lg bg-zinc-100 p-4 text-xs dark:bg-zinc-800" dir="ltr">{
  "token": "YOUR_TOKEN",
  "to": "09120000000",
  "message": "Hello",
  "gateway": "1000"
}</pre>
            </div>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('general.sms_api_error_codes') }}</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('general.code') }}</flux:table.column>
                    <flux:table.column>HTTP</flux:table.column>
                    <flux:table.column>{{ __('general.description') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->errorCodes() as $error)
                        <flux:table.row :key="$error['code']">
                            <flux:table.cell><span dir="ltr">{{ $error['code'] }}</span></flux:table.cell>
                            <flux:table.cell>{{ $error['http'] }}</flux:table.cell>
                            <flux:table.cell>{{ __('general.sms_api_errors.'.$error['code']) }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
