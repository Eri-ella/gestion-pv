<x-app-layout>
    <x-slot name="header">Modifier le PV {{ $pv->reference }}</x-slot>
    <div class="max-w-3xl">
        @include('pvs._form', ['action' => route('pvs.update', $pv), 'pv' => $pv])
    </div>
</x-app-layout>