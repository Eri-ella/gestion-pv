@props(['type' => null])
@php
    $ok = 'bg-primary-soft text-primary-dark';
    [$label, $c] = [
        'brouillon' => ['Brouillon', 'bg-accent-soft text-yellow-800'],
        'valide' => ['Validé', $ok],
        'archive' => ['Archivé', 'bg-slate-200 text-slate-700'],
        'admis' => ['Admis', $ok],
        'ajourne' => ['Ajourné', 'bg-danger-soft text-danger'],
        'rattrapage' => ['Rattrapage', 'bg-info-soft text-info'],
    ][$type] ?? ['—', 'bg-slate-100 text-slate-500'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium $c"]) }}>{{ $label }}</span>
