<x-app-layout>
    <x-slot:header>Nouveau procès-verbal</x-slot:header>
    <div class="max-w-3xl">
        @include('pvs._form', ['action' => route('pvs.store'), 'pv' => null])
    </div>
</x-app-layout>
