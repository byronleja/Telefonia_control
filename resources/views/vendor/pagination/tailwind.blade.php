@if($paginator->hasPages())
<nav class="pagination" role="navigation">
    @if($paginator->onFirstPage())
        <span class="pag-disabled"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg></span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="pag-link" rel="prev"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg></a>
    @endif
    @foreach($elements as $el)
        @if(is_string($el))<span class="pag-dots">{{ $el }}</span>@endif
        @if(is_array($el))
            @foreach($el as $page=>$url)
                @if($page==$paginator->currentPage())
                    <span class="pag-active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="pag-link">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach
    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="pag-link" rel="next"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg></a>
    @else
        <span class="pag-disabled"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg></span>
    @endif
</nav>
@endif