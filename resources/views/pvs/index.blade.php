<x-app-layout>
    <x-slot:header>Procès-verbaux</x-slot:header>

    <form method="GET" action="{{ route('pvs.index') }}" class="card">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div class="sm:col-span-2 lg:col-span-3">
                <x-input-label for="q" value="Référence du PV" />
                <x-text-input id="q" name="q" :value="request('q')" placeholder="Ex : PV-2025-2026-INFO-1" />
            </div>
            <div>
                <x-input-label for="annee_id" value="Année" />
                <select id="annee_id" name="annee_id" class="input">
                    <option value="">Toutes</option>
                    @foreach ($annees as $a)
                        <option value="{{ $a->id }}" @selected(request('annee_id') == $a->id)>{{ $a->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="filiere_id" value="Filière" />
                <select id="filiere_id" name="filiere_id" class="input">
                    <option value="">Toutes</option>
                    @foreach ($filieres as $f)
                        <option value="{{ $f->id }}" @selected(request('filiere_id') == $f->id)>{{ $f->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="niveau_id" value="Niveau" />
                <select id="niveau_id" name="niveau_id" class="input">
                    <option value="">Tous</option>
                    @foreach ($niveaux as $n)
                        <option value="{{ $n->id }}" @selected(request('niveau_id') == $n->id)>{{ $n->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="decision" value="Décision (au moins un étudiant)" />
                <select id="decision" name="decision" class="input">
                    <option value="">Toutes</option>
                    @foreach (['admis' => 'Admis', 'ajourne' => 'Ajourné', 'rattrapage' => 'Rattrapage'] as $v => $l)
                        <option value="{{ $v }}" @selected(request('decision') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="statut" value="Statut" />
                <select id="statut" name="statut" class="input">
                    <option value="">Tous</option>
                    @foreach (['brouillon' => 'Brouillon', 'valide' => 'Validé', 'archive' => 'Archivé'] as $v => $l)
                        <option value="{{ $v }}" @selected(request('statut') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <x-btn>Rechercher</x-btn>
            <x-btn variant="secondary" :href="route('pvs.index')">Réinitialiser</x-btn>
        </div>
    </form>

    <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $pvs->total() }} résultat(s)</p>
        @if (auth()->user()->isRole('administrateur', 'agent_scolarite'))
            <x-btn :href="route('pvs.create')">+ Nouveau PV</x-btn>
        @endif
    </div>

    <div class="card mt-3 overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="th">Référence</th>
                    <th class="th">Promotion</th>
                    <th class="th">Délibération</th>
                    <th class="th">Statut</th>
                    <th class="th"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($pvs as $pv)
                    <tr class="hover:bg-slate-50">
                        <td class="td font-medium">{{ $pv->reference }}</td>
                        <td class="td">{{ $pv->promotion->annee->libelle }} · {{ $pv->promotion->filiere->code }} · {{ $pv->promotion->niveau->libelle }}</td>
                        <td class="td">{{ $pv->date_deliberation?->format('d/m/Y') ?? '—' }}</td>
                        <td class="td"><x-badge :type="$pv->statut" /></td>
                        <td class="td text-right"><a href="{{ route('pvs.show', $pv) }}" class="link">Ouvrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Aucun PV ne correspond à la recherche.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $pvs->links() }}</div>
</x-app-layout>
