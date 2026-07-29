<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? 'O Út đặc sản Huế' }}</title>
    <meta name="description" content="{{ $description ?? 'Đặc sản Huế nhà làm, đặt hàng online nhanh chóng.' }}">
    <meta name="theme-color" content="#5B1F2E">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    @stack('meta')
    @vite(['resources/css/storefront.css', 'resources/js/storefront.js'])
    @livewireStyles
</head>
<body data-root="/" data-page="{{ $page ?? '' }}">
    <x-storefront-header :cart-count="$cartCount ?? 0" />
    <main id="main-content">{{ $slot }}</main>
    <x-storefront-footer />
    @livewireScripts
    @stack('scripts')
</body>
</html>
