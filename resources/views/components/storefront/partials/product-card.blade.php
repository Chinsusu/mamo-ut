@props(['product'])
@php($variant = $product->defaultVariant)

<article class="product-card">
    <a class="product-card__media" href="{{ route('products.show', $product->slug) }}">
        <x-storefront.ui.product-media :product="$product" />
        <span class="product-card__badge">Nhà làm</span>
    </a>
    <div class="product-card__body">
        <p class="eyebrow">{{ $product->category?->name ?? 'Đặc sản Huế' }}</p>
        <h3 class="product-card__name"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <p class="product-card__variant">{{ $product->short_description }}</p>
        <div class="product-card__footer">
            <div>
                <strong class="product-card__price">{{ $variant ? number_format($variant->price_vnd, 0, ',', '.').' đ' : 'Liên hệ' }}</strong>
                @if ($variant)<span class="product-card__variant">{{ $variant->label }} · {{ $variant->stock_quantity > 0 ? 'Còn hàng' : 'Tạm hết' }}</span>@endif
            </div>
            @if ($variant && $variant->stock_quantity > 0)
                <form method="POST" action="{{ route('cart.store', $variant) }}">
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button class="icon-btn" type="submit" aria-label="Thêm {{ $product->name }} vào giỏ">
                        <x-storefront.ui.icon name="plus" />
                    </button>
                </form>
            @endif
        </div>
    </div>
</article>