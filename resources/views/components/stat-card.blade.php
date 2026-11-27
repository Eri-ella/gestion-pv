@props(['label', 'value', 'hint' => null, 'tone' => 'primary'])
@php
    $bar = ['primary' => 'border-l-primary', 'accent' => 'border-l-accent', 'danger' => 'border-l-danger', 'info' => 'border-l-info'][$tone];
@endphp
<div class="card border-l-4 {{ $bar }}">
    <p class="text-sm text-slate-500">{{ $label }}</p>
    <p class="mt-1 text-3xl font-semibold">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endif
</div>
