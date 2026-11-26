@props(['label', 'value', 'hint' => null])
<div class="rounded-xl border border-slate-200 bg-white p-5">
    <p class="text-sm text-slate-500">{{ $label }}</p>
    <p class="mt-1 text-3xl font-semibold">{{ $value }}</p>
    @if ($hint)
    <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p> @endif
</div>