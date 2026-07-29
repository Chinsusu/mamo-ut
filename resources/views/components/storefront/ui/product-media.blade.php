@props([
    'product',
    'image' => null,
    'ratio' => 'square',
])

@php
    $image ??= $product->primaryImage;
    $imagePath = $image?->card_path ?? $image?->product_path;
    $imageUrl = $imagePath
        ? Illuminate\Support\Facades\Storage::disk((string) config('commerce.media.public_disk'))->url($imagePath)
        : null;
    $referenceImage = match ($product->slug) {
        'mam-ruoc' => 'mam-ruot.jpg',
        'mam-ro' => 'mam-ro.jpg',
        'cha-ca-lat' => 'cha-ca-xay.jpg',
        'mam-tom-chua' => 'tom-chua.jpg',
        'mam-tep-chua' => 'tep-chua.jpg',
        default => null,
    };
    $imageUrl ??= $referenceImage ? asset('storefront-assets/images/'.$referenceImage) : null;
    $ratioClass = $ratio === 'product' ? 'aspect-[1/1.05]' : 'aspect-square';
@endphp

<div {{ $attributes->class([$ratioClass, 'storefront-product-media']) }}>
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $image?->alt_text ?: $product->name }}">
    @else
        <div class="storefront-product-media__empty">
            <img src="{{ asset('storefront-assets/images/logo-mark.png') }}" alt="" width="64" height="64">
            <span>{{ $product->name }}</span>
        </div>
    @endif
</div>