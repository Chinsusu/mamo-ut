@props([
    'eyebrow' => null,
    'title',
    'href' => null,
    'linkLabel' => null,
])

<div class="flex flex-wrap items-end justify-between gap-5">
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        <h2 class="mt-2 font-display text-3xl leading-tight text-ink sm:text-4xl">{{ $title }}</h2>
        @if (trim($slot))
            <div class="mt-3 text-base leading-7 text-muted">{{ $slot }}</div>
        @endif
    </div>
    @if ($href && $linkLabel)
        <x-storefront.ui.button :href="$href" variant="quiet" class="px-0">{{ $linkLabel }} <x-heroicon-o-arrow-right class="size-4" /></x-storefront.ui.button>
    @endif
</div>
