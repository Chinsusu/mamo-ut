@props(['product'])
@php($variant = $product->variants->sortBy('price')->first())
<article class="product-card">
    <div class="product-card__media">
        <a href="{{ route('products.show', $product->slug) }}"><img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" width="600" height="600" loading="lazy"></a>
        @if($product->badge)<span class="product-card__badge">{{ $product->badge }}</span>@endif
    </div>
    <div class="product-card__body">
        <h3 class="product-card__name"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <div class="product-card__variant">Giá từ {{ $variant?->label }}</div>
        <div class="rating"><span class="rating-stars">★★★★★</span><span>{{ number_format($product->rating, 1) }}</span></div>
        <div class="product-card__footer"><div><div class="product-card__price">{{ number_format($variant?->price ?? 0, 0, ',', '.') }}đ</div><span class="stock-label">Còn hàng</span></div><a class="product-card__quick" href="{{ route('products.show', $product->slug) }}" aria-label="Xem {{ $product->name }}"><x-icon name="plus" /></a></div>
    </div>
</article>
