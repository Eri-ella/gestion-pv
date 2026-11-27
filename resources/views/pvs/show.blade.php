<x-app-layout>
    <x-slot name="header">PV {{ $pv->reference }}</x-slot>

    @php
        $u = auth()->user();
        $ext = strtolower(pathinfo((string) $pv->fichier_path, PATHINFO_EXTENSION));
    @endphp

    {{-- Barre d'actions selon le rôle --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <x-badge-statut :statut="$pv->statut" class="mr-2" />

        @if ($u->isRole('administrateur', 'agent_scolarite') && $pv->statut !== 'archive')
            <x-btn variant="secondary" :href="route('pvs.edit', $pv)">Modifier</x-btn>
            <x-btn variant="secondary" :href="route('pvs.import', $pv)">Importer les résultats</x-btn>
        @endif

        @if ($u->isRole('administrateur', 'chef_departement') && $pv->statut === 'brouillon')
            <form method="POST" action="{{ route('pvs.valider', $pv) }}" onsubmit="return confirm('Valider ce PV ?')">
                @csrf <x-btn>Valider</x-btn>
            </form>
        @endif

        @if ($u->isRole('administrateur', 'chef_departement') && $pv->statut === 'valide')
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
                        <dt class="text-slate-500">Date de délibération</dt>
                        <dd class="font-medium">{{ $pv->date_deliberation?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Président du jury</dt>
                        <dd class="font-medium">{{ $pv->president_jury ?: '—' }}</dd>
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
                    @forelse ($pv->membres as $m)
                        <li class="flex items-center justify-between gap-2">
                            <span>{{ $m->nom }}@if ($m->fonction) <span class="text-slate-500">· {{ $m->fonction }}</span>@endif</span>
                            @if ($m->president)
                                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">Président</span>
                            @endif
                        </li>
                    @empty
                        <li class="text-slate-500">Aucun membre renseigné.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Colonne droite : aperçu du scan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Aperçu du scan</h2>
                <a href="{{ route('pvs.fichier', $pv) }}" target="_blank"
                    class="text-sm text-brand-600 hover:underline">Ouvrir dans un onglet</a>
            </div>
            @if (! $pv->fichier_path)
                <p class="py-16 text-center text-sm text-slate-400">Aucun fichier scanné.</p>
            @elseif ($ext === 'pdf')
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
                                    class="text-brand-600 hover:underline">{{ $r->etudiant->nom }}
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