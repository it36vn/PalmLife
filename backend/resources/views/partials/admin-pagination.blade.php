@if ($paginator->hasPages())
    <div class="pagination">
        <nav aria-label="{{ $tr['pagination'] }}">
            @if ($paginator->onFirstPage())
                <span>{{ $tr['previous'] }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}">{{ $tr['previous'] }}</a>
            @endif

            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $pageNumber => $url)
                @if ($pageNumber === $paginator->currentPage())
                    <span aria-current="page"><span>{{ $pageNumber }}</span></span>
                @else
                    <a href="{{ $url }}">{{ $pageNumber }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">{{ $tr['next'] }}</a>
            @else
                <span>{{ $tr['next'] }}</span>
            @endif
        </nav>
    </div>
@endif
