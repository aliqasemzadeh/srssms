<div class="{{ $wrapperClass() }}" {{ $attributes }}>
    <table class="{{ $tableClass() }}">
        @if($caption ?? false)
        <caption class="p-5 text-lg font-medium text-left rtl:text-right text-heading">
            {{ $caption }}
        </caption>
        @endif
        @if($head ?? false)
        <thead class="text-sm text-body bg-neutral-secondary-soft border-b border-default">
            {{ $head }}
        </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
        @if($foot ?? false)
        <tfoot class="font-semibold text-heading">
            {{ $foot }}
        </tfoot>
        @endif
    </table>
</div>
