@props(['decision'])
@php
    $map = [
        'admis' => ['Admis', 'bg-emerald-100 text-emerald-800'],
        'ajourne' => ['Ajourné', 'bg-red-100 text-red-800'],
        'rattrapage' => ['Rattrapage', 'bg-amber-100 text-amber-800'],
        'exclu' => ['Exclu', 'bg-slate-800 text-white'],
    ];
    [$label, $c] = $map[$decision] ?? [$decision, 'bg-slate-100 text-slate-700'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium $c"]) }}>{{ $label }}</span>