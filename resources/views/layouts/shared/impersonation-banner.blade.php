@php
    $impersonation = app(\App\Services\Auth\ImpersonationService::class);
@endphp

@if ($impersonation->isImpersonating())
    <div class="sticky top-0 z-50 border-b border-amber-300 bg-amber-50 px-4 py-3 text-amber-950 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-50">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
            <p class="text-sm font-medium">
                {{ __('app.impersonating_as', ['name' => auth()->user()->full_name]) }}
            </p>

            <form method="POST" action="{{ route('impersonation.leave') }}">
                @csrf
                <flux:button type="submit" size="sm" variant="primary" color="amber" icon="undo-2">
                    {{ __('app.leave_impersonation') }}
                </flux:button>
            </form>
        </div>
    </div>
@endif
