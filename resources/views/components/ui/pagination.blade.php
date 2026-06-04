@props(['paginator' => null])

@php
use Illuminate\Support\HtmlString;

if ($paginator) {
    $current = $paginator->currentPage();
    $last    = $paginator->lastPage();

    // Wrap in HtmlString so sanitizeComponentAttribute doesn't double-escape the &
    // when URLs are passed through the component chain (pagination → sub-component → button).
    $safeUrl = fn(string $url) => new HtmlString(e($url));
    $prevUrl = $paginator->onFirstPage() ? null : $safeUrl($paginator->previousPageUrl());
    $nextUrl = $paginator->hasMorePages() ? $safeUrl($paginator->nextPageUrl()) : null;
    $urls    = array_map($safeUrl, $paginator->getUrlRange(1, $last));

    $visible = [];
    for ($p = 1; $p <= $last; $p++) {
        if ($p === 1 || $p === $last || ($p >= $current - 2 && $p <= $current + 2)) {
            $visible[] = $p;
        }
    }

    $items = [];
    $prev  = null;
    foreach ($visible as $p) {
        if ($prev !== null && $p - $prev > 1) {
            $items[] = '...';
        }
        $items[] = $p;
        $prev = $p;
    }
}
@endphp

<nav role="navigation" aria-label="pagination" {{ $attributes->twMerge('flex justify-center sm:justify-end') }}>
    @if($paginator)
        <x-ui.pagination.content>
            <x-ui.pagination.item>
                <x-ui.pagination.previous
                    :href="$prevUrl"
                    :disabled="$paginator->onFirstPage()"
                />
            </x-ui.pagination.item>

            @foreach($items as $item)
                @if($item === '...')
                    <x-ui.pagination.item class="hidden sm:list-item">
                        <x-ui.pagination.ellipsis />
                    </x-ui.pagination.item>
                @else
                    <x-ui.pagination.item class="hidden sm:list-item">
                        <x-ui.pagination.link :href="$urls[$item]" :active="$item === $current">
                            {{ $item }}
                        </x-ui.pagination.link>
                    </x-ui.pagination.item>
                @endif
            @endforeach

            <x-ui.pagination.item>
                <x-ui.pagination.next
                    :href="$nextUrl"
                    :disabled="!$paginator->hasMorePages()"
                />
            </x-ui.pagination.item>
        </x-ui.pagination.content>
    @else
        {{ $slot }}
    @endif
</nav>
