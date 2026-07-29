@php
    $variants = $product->variants->sortBy('sort_order');
    $variant = $product->defaultVariant ?? $variants->first();
    $fallbackImage = match ($product->slug) {
        'mam-ruoc' => asset('storefront-assets/images/mam-ruot.jpg'),
        'mam-ro' => asset('storefront-assets/images/mam-ro.jpg'),
        'cha-ca-lat' => asset('storefront-assets/images/cha-ca-xay.jpg'),
        'mam-tom-chua' => asset('storefront-assets/images/tom-chua.jpg'),
        'mam-tep-chua' => asset('storefront-assets/images/tep-chua.jpg'),
        default => asset('storefront-assets/images/hero-food-spread.jpg'),
    };
    $gallery = $product->images->map(fn ($image) => [
        'url' => Illuminate\Support\Facades\Storage::disk((string) config('commerce.media.public_disk'))->url($image->product_path ?? $image->card_path),
        'alt' => $image->alt_text ?: $product->name,
    ]);
    if ($gallery->isEmpty()) $gallery = collect([['url' => $fallbackImage, 'alt' => $product->name]]);
@endphp

<x-storefront.layout :title="($product->seo_title ?? $product->name).' - '.config('app.name')" page="products">
    <section class="page-hero page-hero--crumb"><div class="container"><div class="breadcrumbs"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><a href="{{ route('products.index') }}">Sản phẩm</a><span>/</span><span>{{ $product->name }}</span></div></div></section>
    <section class="product-detail" data-product-detail>
        <div class="container product-detail__grid">
            <div class="product-gallery">
                <div class="gallery-thumbs" role="list" aria-label="Ảnh sản phẩm">@foreach ($gallery as $image)<button class="gallery-thumb {{ $loop->first ? 'is-active' : '' }}" type="button" data-gallery-thumb data-gallery-url="{{ $image['url'] }}" data-gallery-alt="{{ $image['alt'] }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}"></button>@endforeach</div>
                <div class="gallery-main"><img data-gallery-main src="{{ $gallery->first()['url'] }}" alt="{{ $gallery->first()['alt'] }}"><span class="gallery-badge">Nhà làm</span></div>
            </div>
            <div class="product-buy">
                <span class="eyebrow">{{ $product->category?->name ?? 'Đặc sản Huế nhà làm' }}</span>
                <h1>{{ $product->name }}</h1>
                <p class="product-buy__sub">{{ $product->short_description }}</p>
                <p class="product-buy__price" data-product-price>{{ $variant ? number_format($variant->price_vnd, 0, ',', '.').' đ' : 'Liên hệ' }}</p>
                <span class="stock-label {{ ! $variant || $variant->stock_quantity <= 0 ? 'stock-label--out' : '' }}" data-product-stock>{{ $variant && $variant->stock_quantity > 0 ? 'Còn '.$variant->stock_quantity.' sản phẩm' : 'Tạm hết hàng' }}</span>
                @if ($variant)
                    <form method="POST" action="{{ route('cart.store', $variant) }}" data-product-add-form>
                        @csrf
                        <div class="option-group"><div class="option-group__label"><span>Chọn khối lượng</span><span class="muted small">SKU: <b data-product-sku>{{ $variant->sku }}</b></span></div><div class="variant-list">@foreach ($variants as $item)<button class="variant-btn {{ $item->is($variant) ? 'is-active' : '' }}" type="button" data-product-variant data-cart-url="{{ route('cart.store', $item) }}" data-price="{{ $item->price_vnd }}" data-stock="{{ $item->stock_quantity }}" data-sku="{{ $item->sku }}" {{ $item->stock_quantity <= 0 ? 'disabled' : '' }}>{{ $item->label }}</button>@endforeach</div></div>
                        <div class="option-group"><div class="option-group__label"><span>Số lượng</span><span class="muted small" data-quantity-limit>Tối đa {{ $variant->stock_quantity }}</span></div><div class="quantity-control"><button type="button" data-quantity-minus aria-label="Giảm số lượng"><x-storefront.ui.icon name="minus" /></button><input name="quantity" value="1" min="1" max="{{ min(99, $variant->stock_quantity) }}" inputmode="numeric" aria-label="Số lượng" data-quantity-input><button type="button" data-quantity-plus aria-label="Tăng số lượng"><x-storefront.ui.icon name="plus" /></button></div></div>
                        <div class="buy-actions"><button class="btn btn--primary btn--lg" type="submit" data-add-to-cart {{ $variant->stock_quantity <= 0 ? 'disabled' : '' }}><x-storefront.ui.icon name="cart" /> Thêm vào giỏ</button><a class="btn btn--secondary btn--lg" href="{{ route('cart.index') }}">Xem giỏ hàng</a></div>
                    </form>
                @endif
                <div class="product-assurances"><span class="product-assurance"><x-storefront.ui.icon name="leaf" /> Nguyên liệu rõ ràng</span><span class="product-assurance"><x-storefront.ui.icon name="shield" /> Hướng dẫn bảo quản</span><span class="product-assurance"><x-storefront.ui.icon name="truck" /> Giao toàn quốc</span></div>
            </div>
        </div>
        <div class="container product-content"><article class="product-copy"><span class="eyebrow">Thông tin sản phẩm</span><h2>Vị Huế dễ dùng trong bữa cơm nhà</h2><p>{{ $product->description ?? $product->short_description }}</p><div class="accordion"><details open><summary>Mô tả & thành phần <x-storefront.ui.icon name="chevron-down" /></summary><div class="accordion__content"><p>{{ $product->ingredients ?: 'Thông tin thành phần sẽ được cập nhật theo từng mẻ.' }}</p></div></details><details><summary>Cách dùng gợi ý <x-storefront.ui.icon name="chevron-down" /></summary><div class="accordion__content"><p>{{ $product->usage_instruction ?: 'Dùng theo khẩu vị và hướng dẫn trên nhãn sản phẩm.' }}</p></div></details><details><summary>Bảo quản & hạn dùng <x-storefront.ui.icon name="chevron-down" /></summary><div class="accordion__content"><p>{{ $product->storage_instruction ?: 'Bảo quản theo hướng dẫn trên nhãn sau khi nhận hàng.' }}</p></div></details><details><summary>Giao hàng & đổi trả <x-storefront.ui.icon name="chevron-down" /></summary><div class="accordion__content"><p>Hàng được kiểm tra tồn kho trước khi tạo đơn. O Út sẽ liên hệ xác nhận trước khi chuẩn bị giao.</p></div></details></div></article><aside class="product-facts"><div class="fact-card"><strong><x-storefront.ui.icon name="leaf" /> Nguyên liệu</strong><p>{{ $product->ingredients ?: 'Cập nhật theo thông tin sản phẩm.' }}</p></div><div class="fact-card"><strong><x-storefront.ui.icon name="package" /> Quy cách</strong><p>{{ $variant?->weight_gram ? $variant->weight_gram.' g' : $variant?->label }}</p></div><div class="fact-card"><strong><x-storefront.ui.icon name="truck" /> Giao hàng</strong><p>Phí giao hiển thị trước khi đặt hàng.</p></div></aside></div>
    </section>
    @if ($relatedProducts->isNotEmpty())<section class="section section--white"><div class="container"><div class="section-header"><div><span class="eyebrow">Có thể bạn sẽ thích</span><h2 class="section-heading">Món Huế dùng cùng nhau</h2></div><a class="text-link" href="{{ route('products.index') }}">Xem tất cả <x-storefront.ui.icon name="arrow-right" /></a></div><div class="product-grid product-grid--4">@foreach ($relatedProducts as $relatedProduct)<x-storefront.partials.product-card :product="$relatedProduct" />@endforeach</div></div></section>@endif
</x-storefront.layout>