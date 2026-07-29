@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-brand text-white hover:bg-brand-dark focus-visible:outline-brand',
        'secondary' => 'border border-brand bg-transparent text-brand hover:bg-brand hover:text-white focus-visible:outline-brand',
        'quiet' => 'text-brand hover:text-rust focus-visible:outline-brand',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class(['inline-flex min-h-11 items-center justify-center gap-2 rounded-lg px-5 py-3 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2', $variants[$variant] ?? $variants['primary']]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['inline-flex min-h-11 items-center justify-center gap-2 rounded-lg px-5 py-3 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60', $variants[$variant] ?? $variants['primary']]) }}>
        {{ $slot }}
    </button>
@endif
