<x-app-layout>
    <x-slot:header>Référentiels</x-slot:header>

    @php
        $types = [
            'annees' => [
                'titre' => 'Années', 'items' => $annees, 'cols' => ['Libellé', 'Courante'],
                'row' => fn ($i) => [$i->libelle, $i->courante ? 'Oui' : 'Non'],
                'fields' => [['libelle', 'Libellé (ex : 2025-2026)', 'text'], ['courante', 'Année courante', 'checkbox']],
            ],
            'departements' => [
                'titre' => 'Départements', 'items' => $departements, 'cols' => ['Code', 'Libellé'],
                'row' => fn ($i) => [$i->code, $i->libelle],
                'fields' => [['code', 'Code', 'text'], ['libelle', 'Libellé', 'text']],
            ],
            'filieres' => [
                'titre' => 'Filières', 'items' => $filieres, 'cols' => ['Code', 'Libellé', 'Département'],
                'row' => fn ($i) => [$i->code, $i->libelle, $i->departement?->code],
                'fields' => [['departement_id', 'Département', 'select'], ['code', 'Code', 'text'], ['libelle', 'Libellé', 'text']],
            ],
            'niveaux' => [
                'titre' => 'Niveaux', 'items' => $niveaux, 'cols' => ['Ordre', 'Libellé'],
                'row' => fn ($i) => [$i->ordre, $i->libelle],
                'fields' => [['libelle', 'Libellé (ex : Licence 1)', 'text'], ['ordre', 'Ordre', 'number']],
            ],
        ];
    @endphp

    <div x-data="{ tab: '{{ array_key_first($types) }}' }">
        <div class="mb-6 flex flex-wrap gap-2 border-b border-slate-200">
            @foreach ($types as $key => $t)
                <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="-mb-px border-b-2 px-4 py-2 text-sm font-medium">{{ $t['titre'] }}</button>
            @endforeach
        </div>

        @foreach ($types as $key => $t)
            <div x-show="tab === '{{ $key }}'" x-cloak class="grid gap-6 lg:grid-cols-3">
                <div class="card overflow-x-auto p-0 lg:col-span-2">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach ($t['cols'] as $c)<th class="th">{{ $c }}</th>@endforeach
                                <th class="th"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($t['items'] as $item)
                                <tr>
                                    @foreach ($t['row']($item) as $cell)<td class="td">{{ $cell }}</td>@endforeach
                                    <td class="td text-right">
                                        <form method="POST" action="{{ route('referentiels.destroy', [$key, $item->id]) }}" onsubmit="return confirm('Supprimer cet élément ?')">
                                            @csrf @method('DELETE')
                                            <button class="text-sm font-medium text-danger hover:underline">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($t['cols']) + 1 }}" class="px-4 py-8 text-center text-slate-500">Aucun élément.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('referentiels.store', $key) }}" class="card space-y-3">
                    @csrf
                    <h2 class="font-semibold">Ajouter</h2>
                    @foreach ($t['fields'] as [$name, $label, $type])
                        <div>
                            @if ($type === 'checkbox')
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="checkbox" name="{{ $name }}" value="1" class="rounded border-slate-300 text-primary focus:ring-primary"> {{ $label }}
                                </label>
                            @elseif ($type === 'select')
                                <x-input-label :for="$key.'_'.$name" :value="$label" />
                                <select id="{{ $key.'_'.$name }}" name="{{ $name }}" class="input" required>
                                    <option value="">Choisir…</option>
                                    @foreach ($departements as $d)<option value="{{ $d->id }}">{{ $d->code }} — {{ $d->libelle }}</option>@endforeach
                                </select>
                            @else
                                <x-input-label :for="$key.'_'.$name" :value="$label" />
                                <x-text-input :id="$key.'_'.$name" :type="$type" :name="$name" required />
                            @endif
                        </div>
                    @endforeach
                    <x-btn>Enregistrer</x-btn>
                </form>
            </div>
        @endforeach
    </div>
</x-app-layout>
