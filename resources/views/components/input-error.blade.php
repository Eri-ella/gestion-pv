@props(['messages' => []])
@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-1 space-y-1 text-xs text-danger']) }}>
        @foreach ((array) $messages as $m)
            <li>{{ $m }}</li>
        @endforeach
    </ul>
@endif
