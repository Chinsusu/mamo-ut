@props([
    'name',
    'class' => '',
])

<svg {{ $attributes->merge([
    'class' => trim('icon '.$class),
    'viewBox' => '0 0 24 24',
    'focusable' => 'false',
    'preserveAspectRatio' => 'xMidYMid meet',
]) }} aria-hidden="true">
    <use href="{{ asset('storefront-assets/icons/icons.svg') }}#{{ $name }}"></use>
</svg>