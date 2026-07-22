<?php

use App\Jobs\System\UpdateProjectJob;
use Flux\Flux;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|max:255')]
    public string $command = '';

    public string $output = '';

    public ?int $exitCode = null;

    /**
     * Commands allowed in the quick commands box.
     *
     * @var list<string>
     */
    protected array $quickCommands = [
        'route:clear',
        'cache:clear',
        'view:clear',
        'config:clear',
    ];

    public function softUpdate(): void
    {
        UpdateProjectJob::dispatch(runComposer: false);

        Log::info('Soft update queued.', ['user_id' => auth()->id()]);

        Flux::toast(__('general.update_queued'));
    }

    public function fullUpdate(): void
    {
        UpdateProjectJob::dispatch(runComposer: true);

        Log::info('Full update queued.', ['user_id' => auth()->id()]);

        Flux::toast(__('general.update_queued'));
    }

    public function runQuickCommand(string $command): void
    {
        if (! in_array($command, $this->quickCommands, true)) {
            Flux::toast(variant: 'danger', text: __('general.command_not_allowed'));

            return;
        }

        Artisan::call($command);

        Log::info('Quick artisan command executed.', ['command' => $command, 'user_id' => auth()->id()]);

        Flux::toast(__('general.command_executed', ['command' => $command]));
    }

    public function runCommand(): void
    {
        $this->validate();

        $command = trim((string) preg_replace('/^php\s+artisan\s+/i', '', trim($this->command)));

        try {
            $this->exitCode = Artisan::call($command);
            $this->output = trim(Artisan::output());
        } catch (\Throwable $exception) {
            $this->exitCode = 1;
            $this->output = $exception->getMessage();
        }

        if ($this->output === '') {
            $this->output = __('general.no_output');
        }

        Log::info('Artisan command executed from console box.', [
            'command' => $command,
            'exit_code' => $this->exitCode,
            'user_id' => auth()->id(),
        ]);

        if ($this->exitCode === 0) {
            Flux::toast(__('general.command_executed', ['command' => $command]));
        } else {
            Flux::toast(variant: 'danger', text: __('general.command_failed', ['command' => $command]));
        }
    }
};
?>

<div>
    <x-slot name="title">{{ __('general.functions') }} - {{ config('app.name') }}</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('panels.administrator.dashboard.index') }}" icon="home" />
                <flux:breadcrumbs.item>{{ __('general.system_management') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('general.functions') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            {{-- Box 1: Project update --}}
            <flux:card class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-500/20">
                        <flux:icon.rocket class="size-5 text-violet-600 dark:text-violet-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('general.project_update') }}</flux:heading>
                        <flux:subheading>{{ __('general.project_update_hint') }}</flux:subheading>
                    </div>
                </div>

                <div class="space-y-3">
                    <flux:button variant="primary" color="amber" icon="refresh-cw" class="w-full" wire:click="softUpdate" wire:confirm="{{ __('general.are_you_sure') }}">
                        {{ __('general.soft_update') }}
                    </flux:button>
                    <flux:text size="sm" class="text-center">{{ __('general.soft_update_hint') }}</flux:text>

                    <flux:separator variant="subtle" />

                    <flux:button variant="primary" color="red" icon="rocket" class="w-full" wire:click="fullUpdate" wire:confirm="{{ __('general.are_you_sure') }}">
                        {{ __('general.full_update') }}
                    </flux:button>
                    <flux:text size="sm" class="text-center">{{ __('general.full_update_hint') }}</flux:text>
                </div>
            </flux:card>

            {{-- Box 2: Common commands --}}
            <flux:card class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-500/20">
                        <flux:icon.eraser class="size-5 text-teal-600 dark:text-teal-400" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('general.common_commands') }}</flux:heading>
                        <flux:subheading>{{ __('general.common_commands_hint') }}</flux:subheading>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <flux:button variant="primary" color="blue" icon="route" class="w-full" wire:click="runQuickCommand('route:clear')">
                        route:clear
                    </flux:button>
                    <flux:button variant="primary" color="orange" icon="database" class="w-full" wire:click="runQuickCommand('cache:clear')">
                        cache:clear
                    </flux:button>
                    <flux:button variant="primary" color="violet" icon="eye" class="w-full" wire:click="runQuickCommand('view:clear')">
                        view:clear
                    </flux:button>
                    <flux:button variant="primary" color="teal" icon="settings" class="w-full" wire:click="runQuickCommand('config:clear')">
                        config:clear
                    </flux:button>
                </div>
            </flux:card>
        </div>

        {{-- Box 3: Artisan console --}}
        <flux:card class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-500/20">
                    <flux:icon.terminal class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <flux:heading size="lg">{{ __('general.artisan_console') }}</flux:heading>
                    <flux:subheading>{{ __('general.artisan_console_hint') }}</flux:subheading>
                </div>
            </div>

            <form wire:submit="runCommand" class="flex items-start gap-3">
                <div class="flex-1" dir="ltr">
                    <flux:input wire:model="command" icon="terminal" placeholder="route:list" class="font-mono" />
                </div>
                <flux:button type="submit" variant="primary" color="green" icon="play">
                    {{ __('general.run') }}
                </flux:button>
            </form>

            @if ($output !== '')
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('general.command_output') }}</flux:heading>
                        @if ($exitCode !== null)
                            <flux:badge size="sm" color="{{ $exitCode === 0 ? 'green' : 'red' }}">
                                {{ __('general.exit_code') }}: {{ $exitCode }}
                            </flux:badge>
                        @endif
                    </div>
                    <div dir="ltr" class="max-h-96 overflow-auto rounded-lg bg-zinc-900 p-4 dark:bg-zinc-950">
                        <pre class="whitespace-pre-wrap font-mono text-xs leading-relaxed text-zinc-100">{{ $output }}</pre>
                    </div>
                </div>
            @endif
        </flux:card>
    </div>
</div>
