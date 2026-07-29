@props([
    'title' => null,
    'noindex' => false,
    'checkout' => false,
    'page' => null,
])

@php
    $cartCount = app(App\Domain\Storefront\Services\GuestCart::class)->count(request());
    $page ??= match (request()->route()?->getName()) {
        'home' => 'home',
        'products.index', 'categories.show' => 'products',
        'blog.index', 'posts.show' => 'blog',
        'about' => 'about',
        'policy' => 'policy',
        'contact' => 'contact',
        default => '',
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if ($noindex)<meta name="robots" content="noindex,follow">@endif
    <title>{{ $title ?? config('app.name', 'O Út Đặc Sản Huế') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ $checkout ? 'checkout-page' : 'storefront-page' }}">
    @if ($checkout)
        <header class="site-header">
            <div class="container checkout-header">
                <a class="brand" href="{{ route('home') }}" aria-label="Về trang chủ O Út">
                    <img class="brand__mark" src="{{ asset('storefront-assets/images/logo-mark.png') }}" alt="" width="48" height="48">
                    <span class="brand__text"><span class="brand__name">O Út</span><span class="brand__tagline">đặt hàng an toàn</span></span>
                </a>
                <a class="text-link" href="{{ route('cart.index') }}"><x-storefront.ui.icon name="arrow-left" /> Quay lại giỏ hàng</a>
            </div>
        </header>
    @else
        <a class="skip-link" href="#main-content">Bỏ qua điều hướng</a>
        <div class="utility-bar">
            <div class="container utility-bar__inner">
                <ul class="utility-list" aria-label="Cam kết dịch vụ">
                    <li><x-storefront.ui.icon name="shield" class="icon--sm" /> Cam kết chất lượng</li>
                    <li><x-storefront.ui.icon name="leaf" class="icon--sm" /> Nguyên liệu rõ ràng</li>
                    <li><x-storefront.ui.icon name="truck" class="icon--sm" /> Giao hàng toàn quốc</li>
                </ul>
                <ul class="utility-list utility-list--secondary">
                    <li><x-storefront.ui.icon name="phone" class="icon--sm" /> Hotline sẽ được cập nhật</li>
                    <li><a href="{{ route('orders.track') }}">Theo dõi đơn hàng</a></li>
                </ul>
            </div>
        </div>
        <header class="site-header" role="banner">
            <div class="container header-main">
                <a class="brand" href="{{ route('home') }}" aria-label="O Út đặc sản Huế - Trang chủ">
                    <img class="brand__mark" src="{{ asset('storefront-assets/images/logo-mark.png') }}" alt="" width="48" height="48">
                    <span class="brand__text"><span class="brand__name">O Út</span><span class="brand__tagline">đặc sản Huế</span></span>
                </a>
                <form class="search-bar" action="{{ route('search') }}" method="GET" role="search">
                    <label class="sr-only" for="site-search">Tìm sản phẩm</label>
                    <input id="site-search" name="q" type="search" value="{{ request('q') }}" placeholder="Tìm tôm chua, mắm ruốt...">
                    <button type="submit" aria-label="Tìm kiếm"><x-storefront.ui.icon name="search" /></button>
                </form>
                <div class="header-actions">
                    <a class="header-action" href="{{ route('orders.track') }}"><x-storefront.ui.icon name="receipt" /><span>Tra cứu đơn</span></a>
                    <a class="header-action header-action--cart" href="{{ route('cart.index') }}"><x-storefront.ui.icon name="cart" /><span>Giỏ hàng</span><b class="badge-count">{{ $cartCount }}</b></a>
                </div>
            </div>
            <nav class="main-nav" aria-label="Điều hướng chính">
                <ul class="container nav-list">
                    <li><a class="nav-link {{ $page === 'home' ? 'is-active' : '' }}" href="{{ route('home') }}">Trang chủ</a></li>
                    <li><a class="nav-link {{ $page === 'products' ? 'is-active' : '' }}" href="{{ route('products.index') }}">Sản phẩm</a></li>
                    <li><a class="nav-link {{ $page === 'about' ? 'is-active' : '' }}" href="{{ route('about') }}">Về O Út</a></li>
                    <li><a class="nav-link {{ $page === 'blog' ? 'is-active' : '' }}" href="{{ route('blog.index') }}">Cẩm nang & công thức</a></li>
                    <li><a class="nav-link {{ $page === 'policy' ? 'is-active' : '' }}" href="{{ route('policy') }}">Chính sách</a></li>
                    <li><a class="nav-link {{ $page === 'contact' ? 'is-active' : '' }}" href="{{ route('contact') }}">Liên hệ</a></li>
                </ul>
            </nav>
            <div class="mobile-header">
                <button class="icon-btn" type="button" data-mobile-menu-button aria-label="Mở menu" aria-expanded="false"><x-storefront.ui.icon name="menu" /></button>
                <a class="brand" href="{{ route('home') }}" aria-label="O Út đặc sản Huế"><img class="brand__mark" src="{{ asset('storefront-assets/images/logo-mark.png') }}" alt="" width="48" height="48"><span class="brand__text"><span class="brand__name">O Út</span><span class="brand__tagline">đặc sản Huế</span></span></a>
                <a class="icon-btn" href="{{ route('cart.index') }}" aria-label="Giỏ hàng"><x-storefront.ui.icon name="cart" /><b class="badge-count">{{ $cartCount }}</b></a>
            </div>
            <div class="mobile-menu" data-mobile-menu aria-hidden="true">
                <form class="search-bar" action="{{ route('search') }}" method="GET" role="search"><label class="sr-only" for="mobile-search">Tìm sản phẩm</label><input id="mobile-search" name="q" type="search" placeholder="Tìm đặc sản Huế..."><button type="submit" aria-label="Tìm kiếm"><x-storefront.ui.icon name="search" /></button></form>
                <nav aria-label="Điều hướng di động"><a href="{{ route('home') }}">Trang chủ</a><a href="{{ route('products.index') }}">Sản phẩm</a><a href="{{ route('about') }}">Về O Út</a><a href="{{ route('blog.index') }}">Cẩm nang & công thức</a><a href="{{ route('policy') }}">Chính sách</a><a href="{{ route('contact') }}">Liên hệ</a></nav>
            </div>
        </header>
    @endif

    <main id="main-content">{{ $slot }}</main>

    @if (! $checkout)
        <footer class="site-footer">
            <div class="container footer-main">
                <div class="footer-brand"><a class="brand" href="{{ route('home') }}"><img class="brand__mark" src="{{ asset('storefront-assets/images/logo-mark.png') }}" alt="" width="48" height="48"><span class="brand__text"><span class="brand__name">O Út</span><span class="brand__tagline">đặc sản Huế</span></span></a><p>Những món Huế nhà làm, trình bày rõ ràng để bữa cơm thường ngày thêm một chút vị quê.</p></div>
                <div><h2 class="footer-title">Mua hàng</h2><div class="footer-links"><a href="{{ route('products.index') }}">Sản phẩm</a><a href="{{ route('cart.index') }}">Giỏ hàng</a><a href="{{ route('orders.track') }}">Tra cứu đơn</a></div></div>
                <div><h2 class="footer-title">Thông tin</h2><div class="footer-links"><a href="{{ route('about') }}">Câu chuyện O Út</a><a href="{{ route('blog.index') }}">Cẩm nang món Huế</a><a href="{{ route('policy') }}">Chính sách</a></div></div>
                <div><h2 class="footer-title">Hỗ trợ</h2><div class="footer-links"><a href="{{ route('contact') }}">Liên hệ</a><span>COD & chuyển khoản</span><span>Giao hàng toàn quốc</span></div></div>
            </div>
            <div class="container footer-bottom"><span>© {{ now()->year }} O Út đặc sản Huế</span><span>Thông tin sản phẩm và vận hành sẽ được cập nhật trước khi go-live.</span></div>
        </footer>
        <nav class="bottom-nav" aria-label="Điều hướng di động"><a href="{{ route('home') }}"><x-storefront.ui.icon name="home" />Trang chủ</a><a href="{{ route('products.index') }}"><x-storefront.ui.icon name="grid" />Sản phẩm</a><a href="{{ route('cart.index') }}"><span class="bottom-nav__cart"><x-storefront.ui.icon name="cart" /><b>{{ $cartCount }}</b></span>Giỏ hàng</a><a href="{{ route('contact') }}"><x-storefront.ui.icon name="phone" />Hỗ trợ</a></nav>
    @endif
    @stack('scripts')
</body>
</html>