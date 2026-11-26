<x-app-layout>
    <x-slot name="header">Nouveau procès-verbal</x-slot>
    <div class="max-w-3xl">
        @include('pvs._form', ['action' => route('pvs.store'), 'pv' => null])
    </div>
</x-app-layout>