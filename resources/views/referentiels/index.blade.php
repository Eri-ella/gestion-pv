<x-app-layout>
    <x-slot name="header">Référentiels</x-slot>

    @php
        $types = [
            'annees' => ['Années académiques', $annees, [['libelle', 'Libellé (2025-2026)', 'text'], ['date_debut', 'Début', 'date'], ['date_fin', 'Fin', 'date']]],
            'filieres' => ['Filières', $filieres, [['code', 'Code', 'text'], ['libelle', 'Libellé', 'text']]],
            'niveaux' => ['Niveaux', $niveaux, [['code', 'Code (L1, M2…)', 'text'], ['libelle', 'Libellé', 'text']]],
            'semestres' => ['Semestres', $semestres, [['code', 'Code (S1…)', 'text'], ['libelle', 'Libellé', 'text']]],
        ];
    @endphp

    <div x-data="{ tab: '{{ array_key_first($types) }}' }">
        <div class="mb-6 flex flex-wrap gap-2 border-b border-slate-200">
            @foreach ($types as $key => [$titre])
                <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="-mb-px border-b-2 px-4 py-2 text-sm font-medium">{{ $titre }}</button>
            @endforeach
        </div>

        @foreach ($types as $key => [$titre, $items, $champs])
            <div x-show="tab === '{{ $key }}'" x-cloak class="grid gap-6 lg:grid-cols-3">
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white lg:col-span-2">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                @foreach ($champs as $c)
                                <th class="px-4 py-3">{{ $c[1] }}</th> @endforeach
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($items as $item)
                                <tr>
                                    @foreach ($champs as $c)
                                        @php $v = $item->{$c[0]}; @endphp
                                        <td class="px-4 py-2">{{ $v instanceof \Carbon\Carbon ? $v->format('d/m/Y') : $v }}</td>
                                    @endforeach
                                    <td class="px-4 py-2 text-right">
                                        <form method="POST" action="{{ route('referentiels.destroy', [$key, $item->id]) }}"
                                            onsubmit="return confirm('Supprimer cet élément ?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:underline">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($champs) + 1 }}" class="px-4 py-8 text-center text-slate-500">Aucun
                                        élément.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('referentiels.store', $key) }}"
                    class="space-y-3 rounded-xl border border-slate-200 bg-white p-5">
                    @csrf
                    <h2 class="font-semibold">Ajouter</h2>
                    @foreach ($champs as [$name, $label, $type])
                        <div>
                            <x-input-label :for="$key . '_' . $name" :value="$label" />
                            <x-text-input :id="$key . '_' . $name" :type="$type" :name="$name" class="mt-1 w-full" required />
                        </div>
                    @endforeach
                    <x-btn>Enregistrer</x-btn>
                </form>
            </div>
        @endforeach
    </div>
</x-app-layout>