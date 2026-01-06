@if ($paginator->hasPages())
    <div class="dataTable-bottom">
        <nav class="dataTable-pagination">
            <ul class="dataTable-pagination-list">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="pager disabled"><a href="#" aria-disabled="true">‹</a></li>
                @else
                    <li class="pager"><a href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a></li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="ellipsis"><a href="#">{{ $element }}</a></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="active"><a href="#">{{ $page }}</a></li>
                            @else
                                <li><a href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="pager"><a href="{{ $paginator->nextPageUrl() }}" rel="next">›</a></li>
                @else
                    <li class="pager disabled"><a href="#" aria-disabled="true">›</a></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
