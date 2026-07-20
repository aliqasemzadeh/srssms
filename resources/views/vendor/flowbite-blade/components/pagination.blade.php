@if($paginator)
<nav aria-label="Page navigation" {{ $attributes }}>
    @if($showInfo)
        <div class="flex flex-col items-center">
            <span class="text-sm text-body">
                Showing <span class="font-semibold text-heading">{{ $paginator->firstItem() ?? 0 }}</span> to <span class="font-semibold text-heading">{{ $paginator->lastItem() ?? 0 }}</span> of <span class="font-semibold text-heading">{{ $paginator->total() }}</span> Entries
            </span>
            <div class="inline-flex mt-2 xs:mt-0">
                <a href="{{ $paginator->previousPageUrl() }}" class="flex items-center justify-center px-3 h-8 text-sm font-medium text-white bg-dark rounded-s hover:bg-dark-strong {{ $paginator->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }}">
                    @if($icons)
                        <x-fwb-o-arrow-left class="w-3.5 h-3.5 me-2 rtl:rotate-180" />
                    @endif
                    {{ $previousLabel }}
                </a>
                <a href="{{ $paginator->nextPageUrl() }}" class="flex items-center justify-center px-3 h-8 text-sm font-medium text-white bg-dark border-0 border-s border-default rounded-e hover:bg-dark-strong {{ $paginator->hasMorePages() ? '' : 'cursor-not-allowed opacity-50' }}">
                    {{ $nextLabel }}
                    @if($icons)
                        <x-fwb-o-arrow-right class="w-3.5 h-3.5 ms-2 rtl:rotate-180" />
                    @endif
                </a>
            </div>
        </div>
    @elseif($variant === 'simple')
        <div class="flex">
            <a href="{{ $paginator->previousPageUrl() }}" class="flex items-center justify-center px-3 h-8 text-sm font-medium text-body bg-neutral-primary-soft border border-default rounded-base hover:bg-neutral-secondary-medium hover:text-heading me-3 {{ $paginator->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }}">
                @if($icons)
                    <x-fwb-o-arrow-left class="w-3.5 h-3.5 me-2 rtl:rotate-180" />
                @endif
                {{ $previousLabel }}
            </a>
            <a href="{{ $paginator->nextPageUrl() }}" class="flex items-center justify-center px-3 h-8 text-sm font-medium text-body bg-neutral-primary-soft border border-default rounded-base hover:bg-neutral-secondary-medium hover:text-heading {{ $paginator->hasMorePages() ? '' : 'cursor-not-allowed opacity-50' }}">
                {{ $nextLabel }}
                @if($icons)
                    <x-fwb-o-arrow-right class="w-3.5 h-3.5 ms-2 rtl:rotate-180" />
                @endif
            </a>
        </div>
    @else
        <ul class="inline-flex -space-x-px text-sm">
            <li>
                <a href="{{ $paginator->previousPageUrl() }}" class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-body bg-neutral-primary-soft border border-e-0 border-default rounded-s-base hover:bg-neutral-secondary-medium hover:text-heading {{ $paginator->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }}">
                    @if($icons)
                        <span class="sr-only">Previous</span>
                        <x-fwb-o-chevron-left class="w-2.5 h-2.5 rtl:rotate-180" />
                    @else
                        {{ $previousLabel }}
                    @endif
                </a>
            </li>
            @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            <li>
                <a href="{{ $url }}" class="flex items-center justify-center px-3 h-8 leading-tight {{ $page == $paginator->currentPage() ? 'text-fg-brand-strong border border-default bg-brand-softer hover:bg-brand-soft' : 'text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium hover:text-heading' }}" {{ $page == $paginator->currentPage() ? 'aria-current=page' : '' }}>{{ $page }}</a>
            </li>
            @endforeach
            <li>
                <a href="{{ $paginator->nextPageUrl() }}" class="flex items-center justify-center px-3 h-8 leading-tight text-body bg-neutral-primary-soft border border-default rounded-e-base hover:bg-neutral-secondary-medium hover:text-heading {{ $paginator->hasMorePages() ? '' : 'cursor-not-allowed opacity-50' }}">
                    @if($icons)
                        <span class="sr-only">Next</span>
                        <x-fwb-o-chevron-right class="w-2.5 h-2.5 rtl:rotate-180" />
                    @else
                        {{ $nextLabel }}
                    @endif
                </a>
            </li>
        </ul>
    @endif
</nav>
@endif
