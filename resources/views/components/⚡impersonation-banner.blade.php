<?php

use App\Services\Auth\ImpersonationService;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public function leave(ImpersonationService $impersonation): void
    {
        $impersonation->leave();

        Flux::toast(__('app.impersonation_stopped'));

        $this->redirect(route('panels.administrator.user-management.user.index'), navigate: true);
    }
};
?>

<div>
    @if (app(ImpersonationService::class)->isImpersonating())
        <div class="sticky top-0 z-50 border-b border-amber-300 bg-amber-50 px-4 py-3 text-amber-950 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-50">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm font-medium">
                    {{ __('app.impersonating_as', ['name' => auth()->user()->full_name]) }}
                </p>

                <flux:button type="button" size="sm" variant="primary" color="amber" icon="undo-2" wire:click="leave">
                    {{ __('app.leave_impersonation') }}
                </flux:button>
            </div>
        </div>
    @endif
</div>
