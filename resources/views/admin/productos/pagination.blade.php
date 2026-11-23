@if ($paginator->hasPages())
    <nav aria-label="Paginación de productos" class="inline-flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg bg-uch-bg text-uch-muted opacity-60">
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior"
               class="flex h-9 w-9 items-center justify-center rounded-lg text-uch-muted transition-colors hover:bg-uch-bg">
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="flex h-9 w-9 items-center justify-center text-label-md text-uch-muted">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="flex h-9 w-9 items-center justify-center rounded-lg bg-uch-blue text-label-md font-bold text-white shadow-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Página {{ $page }}"
                           class="flex h-9 w-9 items-center justify-center rounded-lg text-label-md text-uch-text transition-colors hover:bg-uch-bg">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Página siguiente"
               class="flex h-9 w-9 items-center justify-center rounded-lg text-uch-muted transition-colors hover:bg-uch-bg">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </a>
        @else
            <span class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg bg-uch-bg text-uch-muted opacity-60">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </span>
        @endif
    </nav>
@endif
