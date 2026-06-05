@if ($paginator->hasPages())
<nav class="pagination" role="navigation" aria-label="Pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="page-link" style="opacity:0.4;cursor:not-allowed" aria-disabled="true" aria-label="Previous page">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-link" rel="prev" aria-label="Previous page">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </a>
    @endif

    {{-- Page numbers --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="page-link" style="opacity:0.5;cursor:default" aria-hidden="true">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-link active" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-link" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="page-link" rel="next" aria-label="Next page">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </a>
    @else
        <span class="page-link" style="opacity:0.4;cursor:not-allowed" aria-disabled="true" aria-label="Next page">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </span>
    @endif
</nav>
@endif
