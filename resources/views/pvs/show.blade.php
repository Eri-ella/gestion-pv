<x-app-layout>
    <x-slot name="header">PV {{ $pv->reference }}</x-slot>

    @php
        $u = auth()->user();
        $ext = strtolower(pathinfo($pv->fichier_path, PATHINFO_EXTENSION));
    @endphp

    {{-- Barre d'actions selon le rôle --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <x-badge-statut :statut="$pv->statut" class="mr-2" />

        @if ($u->aRole('administrateur', 'agent_scolarite') && $pv->statut !== 'archive')
            <x-btn variant="secondary" :href="route('pvs.edit', $pv)">Modifier</x-btn>
            <x-btn variant="secondary" :href="route('pvs.import', $pv)">Importer les résultats</x-btn>
        @endif

        @if ($u->aRole('administrateur', 'direction') && $pv->statut === 'brouillon')
            <form method="POST" action="{{ route('pvs.valider', $pv) }}" onsubmit="return confirm('Valider ce PV ?')">
                @csrf <x-btn>Valider</x-btn>
            </form>
        @endif

        @if ($u->aRole('administrateur', 'direction') && $pv->statut === 'valide')
            <form method="POST" action="{{ route('pvs.archiver', $pv) }}" onsubmit="return confirm('Archiver ce PV ?')">
                @csrf <x-btn variant="secondary">Archiver</x-btn>
            </form>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Colonne gauche : informations --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="mb-3 font-semibold">Informations</h2>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-slate-500">Promotion</dt>
                        <dd class="font-medium">{{ $pv->promotion->libelle }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Session</dt>
                        <dd class="font-medium capitalize">{{ $pv->session }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Date de délibération</dt>
                        <dd class="font-medium">{{ $pv->date_deliberation->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Président du jury</dt>
                        <dd class="font-medium">{{ $pv->president_jury }}</dd>
                    </div>
                    @if ($pv->valide_par)
                        <div>
                            <dt class="text-slate-500">Validé par</dt>
                            <dd class="font-medium">{{ $pv->validateur->name }} le
                                {{ $pv->date_validation->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="mb-3 font-semibold">Membres du jury</h2>
                <ul class="space-y-2 text-sm">
                    @foreach ($pv->membres as $m)
                        <li>{{ $m->nom_complet }} @if ($m->fonction) <span class="text-slate-500">·
                        {{ $m->fonction }}</span> @endif</li>
                    @endforeach
                </ul>
            </div>

            {{-- Partage par lien temporaire --}}
            @if ($u->aRole('administrateur', 'direction', 'agent_scolarite'))
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="mb-3 font-semibold">Lien de partage</h2>
                    <form method="POST" action="{{ route('pvs.liens.store', $pv) }}" class="flex items-end gap-2">
                        @csrf
                        <div class="flex-1">
                            <x-input-label for="duree" value="Durée de validité" />
                            <x-select id="duree" name="duree" class="mt-1 w-full">
                                <option value="24">24 heures</option>
                                <option value="72">3 jours</option>
                                <option value="168">7 jours</option>
                            </x-select>
                        </div>
                        <x-btn>Générer</x-btn>
                    </form>

                    <ul class="mt-4 space-y-2 text-xs">
                        @forelse ($pv->liens as $l)
                            <li class="rounded-md bg-slate-50 p-2" x-data="{ ok: false }">
                                <p class="break-all text-slate-600">{{ route('partage.show', $l->token) }}</p>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="{{ $l->estValide() ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ $l->estValide() ? 'Expire le ' . $l->expire_le->format('d/m/Y H:i') : 'Expiré' }}
                                    </span>
                                    @if ($l->estValide())
                                        <button type="button" class="text-indigo-600 hover:underline"
                                            @click="navigator.clipboard.writeText('{{ route('partage.show', $l->token) }}'); ok = true; setTimeout(() => ok = false, 1500)"
                                            x-text="ok ? 'Copié' : 'Copier'"></button>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="text-slate-500">Aucun lien généré.</li>
                        @endforelse
                    </ul>
                </div>
            @endif
        </div>

        {{-- Colonne droite : aperçu du scan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Aperçu du scan</h2>
                <a href="{{ route('pvs.fichier', $pv) }}" target="_blank"
                    class="text-sm text-indigo-600 hover:underline">Ouvrir dans un onglet</a>
            </div>
            @if ($ext === 'pdf')
                <iframe src="{{ route('pvs.fichier', $pv) }}"
                    class="h-[70vh] w-full rounded-md border border-slate-200"></iframe>
            @else
                <img src="{{ route('pvs.fichier', $pv) }}" alt="Scan du PV {{ $pv->reference }}"
                    class="max-h-[70vh] w-full rounded-md border border-slate-200 object-contain">
            @endif
        </div>
    </div>

    {{-- Résultats --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white" x-data="{ q: '' }">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
            <h2 class="font-semibold">Résultats ({{ $pv->resultats->count() }})</h2>
            <input type="search" x-model="q" placeholder="Filtrer par nom ou matricule…"
                class="w-full rounded-md border-slate-300 text-sm sm:w-72">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Matricule</th>
                        <th class="px-4 py-3">Nom et prénoms</th>
                        <th class="px-4 py-3 text-right">Moyenne</th>
                        <th class="px-4 py-3 text-right">Crédits</th>
                        <th class="px-4 py-3">Décision</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pv->resultats as $r)
                        <tr x-show="$el.textContent.toLowerCase().includes(q.toLowerCase())">
                            <td class="px-4 py-2 font-mono text-xs">{{ $r->etudiant->matricule }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('etudiants.show', $r->etudiant) }}"
                                    class="text-indigo-600 hover:underline">{{ $r->etudiant->nom }}
                                    {{ $r->etudiant->prenoms }}</a>
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ $r->moyenne !== null ? number_format($r->moyenne, 2, ',', ' ') : '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ $r->credits_valides ?? '—' }}</td>
                            <td class="px-4 py-2"><x-badge-decision :decision="$r->decision" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucun résultat saisi pour ce PV.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>