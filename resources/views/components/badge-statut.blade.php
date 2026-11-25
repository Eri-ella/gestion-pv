@props(['statut'])
@php
    $map = [
        'brouillon' => ['Brouillon', 'bg-amber-100 text-amber-800'],
        'valide' => ['Validé', 'bg-emerald-100 text-emerald-800'],
        'archive' => ['Archivé', 'bg-slate-200 text-slate-700'],
    ];
    [$label, $c] = $map[$statut] ?? [$statut, 'bg-slate-100 text-slate-700'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium $c"]) }}>{{ $label }}</span>