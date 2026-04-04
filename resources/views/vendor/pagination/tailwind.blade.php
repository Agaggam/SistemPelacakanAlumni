@if ($paginator->hasPages())
    <style>
        .custom-pagination { display: flex; flex-direction: column; align-items: center; gap: 16px; margin-top: 24px; width: 100%; font-family: 'Inter', sans-serif; }
        .custom-pagination-info { font-size: 13px; color: var(--muted); }
        .custom-pagination-info span { font-weight: 700; color: var(--text); }
        
        .custom-pagination-links { display: flex; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 8px; overflow: hidden; }
        
        .c-page-item {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 8px 14px; min-width: 38px;
            background: var(--card); color: var(--text);
            border: 1px solid var(--border); border-left: none;
            font-size: 13px; font-weight: 600; text-decoration: none;
            transition: all 0.2s ease; cursor: pointer;
        }
        .c-page-item:first-child { border-left: 1px solid var(--border); }
        
        .c-page-item:hover:not(.c-disabled):not(.c-active) {
            background: rgba(99,102,241,0.1); color: var(--accent-light);
        }
        
        .c-page-item.c-active { background: var(--accent); color: white; border-color: var(--accent); cursor: default; }
        .c-page-item.c-disabled { opacity: 0.4; cursor: not-allowed; }
        .c-page-item svg { width: 16px; height: 16px; }

        @media (min-width: 640px) {
            .custom-pagination { flex-direction: row; justify-content: space-between; }
        }
    </style>

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="custom-pagination">
        <div class="custom-pagination-info">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span>{{ $paginator->firstItem() }}</span> {!! __('to') !!} <span>{{ $paginator->lastItem() }}</span>
            @else
                <span>{{ $paginator->count() }}</span>
            @endif
            {!! __('of') !!}
            <span>{{ $paginator->total() }}</span> {!! __('results') !!}
        </div>

        <div class="custom-pagination-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="c-page-item c-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="c-page-item" aria-label="{{ __('pagination.previous') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="c-page-item c-disabled" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="c-page-item c-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="c-page-item">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="c-page-item" aria-label="{{ __('pagination.next') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </a>
            @else
                <span class="c-page-item c-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
