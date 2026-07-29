@php
    $heroImageUrl = filled($heroBanner?->image_path)
        ? Illuminate\Support\Facades\Storage::disk((string) config('commerce.media.public_disk'))->url($heroBanner->image_path)
        : asset('storefront-assets/images/hero-ouut.jpg');
    $categoryImages = [
        'mam-ruoc' => 'mam-ruot-thumb.jpg',
        'mam-ro' => 'mam-ro-thumb.jpg',
        'cha-ca-lat' => 'cha-ca-xay-thumb.jpg',
        'mam-tom-chua' => 'tom-chua-thumb.jpg',
        'mam-tep-chua' => 'tep-chua-thumb.jpg',
    ];
@endphp

<x-storefront.layout :title="config('app.name')" page="home">
    <section class="hero">
        <div class="container">
            <div class="hero-shell">
                <div class="hero-copy">
                    <span class="eyebrow">Đặc sản Huế nhà làm</span>
                    <h1>{{ $heroBanner?->title ?? 'Gửi trọn hương vị Huế vào bữa cơm nhà' }}</h1>
                    <p>{{ $heroBanner?->subtitle ?? 'Tôm chua, tép chua, chả cá lạt, mắm rò và mắm ruốt làm theo mẻ nhỏ, thông tin rõ ràng.' }}</p>
                    <div class="hero-actions"><a class="btn btn--primary btn--lg" href="{{ $heroBanner?->link_url ?: route('products.index') }}">Chọn món ngay <x-storefront.ui.icon name="arrow-right" /></a><a class="btn btn--secondary btn--lg" href="{{ route('about') }}">Khám phá câu chuyện</a></div>
                    <div class="hero-notes"><span class="hero-note"><x-storefront.ui.icon name="leaf" class="icon--sm" /> Làm theo mẻ nhỏ</span><span class="hero-note"><x-storefront.ui.icon name="shield" class="icon--sm" /> Rõ cách dùng & bảo quản</span></div>
                </div>
                <div class="hero-media"><img src="{{ $heroImageUrl }}" alt="Mâm đặc sản Huế O Út"><div class="hero-badge"><strong>Nhà làm theo mẻ</strong><span>Ưu tiên hương vị ổn định, đóng gói gọn gàng và thông tin minh bạch.</span></div></div>
            </div>
            <div class="trust-strip"><div class="trust-item"><span class="trust-item__icon"><x-storefront.ui.icon name="leaf" /></span><span><strong>Nguyên liệu rõ ràng</strong><span>Xem thông tin trước khi chọn.</span></span></div><div class="trust-item"><span class="trust-item__icon"><x-storefront.ui.icon name="package" /></span><span><strong>Đóng gói cẩn thận</strong><span>Phù hợp gửi đi xa.</span></span></div><div class="trust-item"><span class="trust-item__icon"><x-storefront.ui.icon name="truck" /></span><span><strong>Giao hàng toàn quốc</strong><span>Phí hiển thị trước khi đặt.</span></span></div><div class="trust-item"><span class="trust-item__icon"><x-storefront.ui.icon name="shield" /></span><span><strong>Đặt hàng không cần tài khoản</strong><span>Guest checkout đơn giản.</span></span></div></div>
        </div>
    </section>

    <section class="section--compact"><div class="container"><div class="section-header"><div class="section-header__copy"><span class="eyebrow">Chọn món dễ dàng</span><h2 class="section-heading">Năm vị Huế quen mà nhớ</h2><p class="section-lead">Danh mục ngắn, dễ tìm để khách có thể đi thẳng vào đúng món cần dùng.</p></div><a class="text-link" href="{{ route('products.index') }}">Xem tất cả <x-storefront.ui.icon name="arrow-right" /></a></div><div class="category-strip">@foreach ($categories as $category)<a class="category-chip" href="{{ route('categories.show', $category) }}"><img src="{{ asset('storefront-assets/images/'.($categoryImages[$category->slug] ?? 'mam-ruot-thumb.jpg')) }}" alt=""><span><strong>{{ $category->name }}</strong><span class="small muted">{{ $category->products_count }} sản phẩm</span></span></a>@endforeach</div></div></section>

    <section class="section section--white"><div class="container"><div class="section-header"><div class="section-header__copy"><span class="eyebrow">Mẻ mới được yêu thích</span><h2 class="section-heading">Sản phẩm nổi bật</h2><p class="section-lead">Giá, biến thể và trạng thái hiển thị ngay trên card để quyết định mua nhanh hơn.</p></div><a class="text-link" href="{{ route('products.index') }}">Khám phá cửa hàng <x-storefront.ui.icon name="arrow-right" /></a></div><div class="product-grid">@forelse ($featuredProducts as $product)<x-storefront.partials.product-card :product="$product" />@empty<div class="empty-state"><h2>Đang cập nhật sản phẩm</h2><p>O Út sẽ sớm bổ sung các món nhà làm.</p></div>@endforelse</div></div></section>

    <section class="section"><div class="container story-grid"><article class="story-copy"><span class="eyebrow">Câu chuyện O Út</span><h2 class="section-heading">Món ngon xứ Huế làm từ cái tâm của người nhà</h2><p>O Út không cố biến món nhà làm thành thứ xa hoa. Điều quan trọng là hương vị dễ nhớ, nguyên liệu và cách bảo quản được nói rõ, mỗi hũ đến tay khách vẫn gọn gàng và tử tế.</p><div class="quote-card"><blockquote>“Đặc sản ngon không chỉ nằm ở vị đậm, mà ở cảm giác yên tâm khi mở nắp.”</blockquote><cite>— Tinh thần thương hiệu O Út</cite></div><a class="btn btn--secondary" href="{{ route('about') }}" style="margin-top:24px">Đọc câu chuyện <x-storefront.ui.icon name="arrow-right" /></a></article><div class="story-media"><img src="{{ asset('storefront-assets/images/story-ouut.jpg') }}" alt="Người phụ nữ Huế chuẩn bị món ăn nhà làm"><div class="story-media__caption"><strong>Làm theo mẻ nhỏ</strong><span class="small muted">Dễ kiểm soát chất lượng, ngày làm và khâu đóng gói.</span></div></div></div></section>

    @if ($latestPosts->isNotEmpty())<section class="section section--beige"><div class="container"><div class="section-header"><div class="section-header__copy"><span class="eyebrow">Cẩm nang món Huế</span><h2 class="section-heading">Ăn ngon hơn khi hiểu món</h2><p class="section-lead">Nội dung thực tế giúp khách biết cách dùng, bảo quản và chọn đúng sản phẩm.</p></div><a class="text-link" href="{{ route('blog.index') }}">Xem cẩm nang <x-storefront.ui.icon name="arrow-right" /></a></div><div class="article-grid">@foreach ($latestPosts as $index => $post)
    @php
        $postImage = filled($post->image_path)
            ? Illuminate\Support\Facades\Storage::disk((string) config('commerce.media.public_disk'))->url($post->image_path)
            : asset('storefront-assets/images/blog-'.(($index % 4) + 1).'.jpg');
    @endphp
    <article class="article-card"><a class="article-card__media" href="{{ route('posts.show', $post) }}"><img src="{{ $postImage }}" alt="{{ $post->title }}"></a><div class="article-card__body"><p class="article-card__meta">{{ $post->published_at?->format('d.m.Y') }}</p><h3><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h3><p>{{ $post->excerpt }}</p></div></article>
@endforeach</div></div></section>@endif

    <section class="section"><div class="container"><div class="cta-banner"><div class="cta-banner__image"><img src="{{ asset('storefront-assets/images/hue-river.jpg') }}" alt="Khung cảnh sông nước Huế"></div><div class="cta-banner__copy"><span class="eyebrow" style="color:#f0c9ad">Từ Huế gửi đi</span><h2>Mang một chút vị quê về bàn ăn hôm nay</h2><p>Chọn món, thêm vào giỏ và hoàn tất đơn mà không cần đăng ký tài khoản. O Út sẽ xác nhận trước khi chuẩn bị hàng.</p><a class="btn btn--light btn--lg" href="{{ route('products.index') }}">Chọn món ngay <x-storefront.ui.icon name="cart" /></a></div></div></div></section>
</x-storefront.layout>