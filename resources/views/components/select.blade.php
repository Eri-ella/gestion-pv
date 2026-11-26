<select {{ $attributes->merge(['class' => 'rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500']) }}>
    {{ $slot }}
</select>