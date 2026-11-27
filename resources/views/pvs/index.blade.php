<x-app-layout>
    <x-slot name="header">Procès-verbaux</x-slot>

    <form method="GET" action="{{ route('pvs.index') }}" class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <x-input-label for="q" value="Nom ou matricule de l'étudiant" />
                <x-text-input id="q" name="q" class="mt-1 w-full" :value="request('q')"
                    placeholder="Ex : MAT001 ou Dossou" />
            </div>
            <div>
                <x-input-label for="annee_id" value="Année" />
                <x-select id="annee_id" name="annee_id" class="mt-1 w-full">
                    <option value="">Toutes</option>
                    @foreach ($annees as $a)
                        <option value="{{ $a->id }}" @selected(request('annee_id') == $a->id)>{{ $a->libelle }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="filiere_id" value="Filière" />
                <x-select id="filiere_id" name="filiere_id" class="mt-1 w-full">
                    <option value="">Toutes</option>
                    @foreach ($filieres as $f)
                        <option value="{{ $f->id }}" @selected(request('filiere_id') == $f->id)>{{ $f->code }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="niveau_id" value="Niveau" />
                <x-select id="niveau_id" name="niveau_id" class="mt-1 w-full">
                    <option value="">Tous</option>
                    @foreach ($niveaux as $n)
                        <option value="{{ $n->id }}" @selected(request('niveau_id') == $n->id)>{{ $n->code }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="semestre_id" value="Semestre" />
                <x-select id="semestre_id" name="semestre_id" class="mt-1 w-full">
                    <option value="">Tous</option>
                    @foreach ($semestres as $s)
                        <option value="{{ $s->id }}" @selected(request('semestre_id') == $s->id)>{{ $s->code }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="session" value="Session" />
                <x-select id="session" name="session" class="mt-1 w-full">
                    <option value="">Toutes</option>
                    <option value="normale" @selected(request('session') === 'normale')>Normale</option>
                    <option value="rattrapage" @selected(request('session') === 'rattrapage')>Rattrapage</option>
                </x-select>
            </div>
            <div>
                <x-input-label for="decision" value="Décision" />
                <x-select id="decision" name="decision" class="mt-1 w-full">
                    <option value="">Toutes</option>
                    @foreach (['admis' => 'Admis', 'ajourne' => 'Ajourné', 'rattrapage' => 'Rattrapage', 'exclu' => 'Exclu'] as $v => $l)
                        <option value="{{ $v }}" @selected(request('decision') === $v)>{{ $l }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="statut" value="Statut" />
                <x-select id="statut" name="statut" class="mt-1 w-full">
                    <option value="">Tous</option>
                    @foreach (['brouillon' => 'Brouillon', 'valide' => 'Validé', 'archive' => 'Archivé'] as $v => $l)
                        <option value="{{ $v }}" @selected(request('statut') === $v)>{{ $l }}</option>
                    @endforeach
                </x-select>
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
            <x-btn :href="route('pvs.create')">Nouveau PV</x-btn>
        @endif
    </div>

    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Référence</th>
                    <th class="px-4 py-3">Promotion</th>
                    <th class="px-4 py-3">Session</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($pvs as $pv)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $pv->reference }}</td>
                        <td class="px-4 py-3">{{ $pv->promotion->libelle }}</td>
                        <td class="px-4 py-3 capitalize">{{ $pv->session }}</td>
                        <td class="px-4 py-3">{{ $pv->date_deliberation->format('d/m/Y') }}</td>
                        <td class="px-4 py-3"><x-badge-statut :statut="$pv->statut" /></td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('pvs.show', $pv) }}"
                                class="text-brand-600 hover:underline">Ouvrir</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">Aucun PV ne correspond à la recherche.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $pvs->withQueryString()->links() }}</div>
</x-app-layout>