@props(['variant' => 'primary', 'href' => null])
@php
    $c = [
        'primary' => 'bg-primary text-white hover:bg-primary-dark',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
        'accent' => 'bg-accent text-slate-900 hover:opacity-90',
        'danger' => 'bg-danger text-white hover:opacity-90',
    ][$variant];
    $class = "inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition $c";
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $class]) }}>{{ $slot }}</button>
@endif
