@props(['name', 'class' => 'icon'])
<svg {{ $attributes->merge(['class' => $class, 'aria-hidden' => 'true']) }}>
    <use href="{{ asset('assets/icons/icons.svg') }}#{{ $name }}"></use>
</svg>
