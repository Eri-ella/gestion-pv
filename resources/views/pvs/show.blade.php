<x-app-layout>
    <x-slot:header>PV {{ $pv->reference }}</x-slot:header>

    @php
        $u = auth()->user();
        $p = $pv->promotion;
        $ext = $pv->fichier_path ? strtolower(pathinfo($pv->fichier_path, PATHINFO_EXTENSION)) : null;
    @endphp

    {{-- Actions selon le rôle --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <x-badge :type="$pv->statut" class="mr-2" />

        @if ($u->isRole('administrateur', 'agent_scolarite') && $pv->statut !== 'archive')
            <x-btn variant="secondary" :href="route('pvs.edit', $pv)">Modifier</x-btn>
            <x-btn variant="secondary" :href="route('pvs.import')">Importer des résultats</x-btn>
        @endif

        @if ($u->isRole('administrateur', 'chef_departement') && $pv->statut === 'brouillon')
            <form method="POST" action="{{ route('pvs.valider', $pv) }}" onsubmit="return confirm('Valider ce PV ?')">
                @csrf <x-btn>Valider</x-btn>
            </form>
        @endif

        @if ($u->isRole('administrateur', 'chef_departement') && $pv->statut === 'valide')
            <form method="POST" action="{{ route('pvs.archiver', $pv) }}" onsubmit="return confirm('Archiver ce PV ?')">
                @csrf <x-btn variant="accent">Archiver</x-btn>
            </form>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 font-semibold">Informations</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Année</dt><dd class="font-medium">{{ $p->annee->libelle }}</dd></div>
                    <div><dt class="text-slate-500">Filière</dt><dd class="font-medium">{{ $p->filiere->libelle }} ({{ $p->filiere->code }})</dd></div>
                    <div><dt class="text-slate-500">Niveau</dt><dd class="font-medium">{{ $p->niveau->libelle }}</dd></div>
                    <div><dt class="text-slate-500">Date de délibération</dt><dd class="font-medium">{{ $pv->date_deliberation?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Président du jury</dt><dd class="font-medium">{{ $pv->president_jury ?: '—' }}</dd></div>
                    @if ($pv->valide_par)
                        <div><dt class="text-slate-500">Validé par</dt><dd class="font-medium">{{ $pv->validateur?->name }} le {{ $pv->date_validation?->format('d/m/Y') }}</dd></div>
                    @endif
                </dl>
            </div>

            @if ($pv->membres->isNotEmpty())
                <div class="card">
                    <h2 class="mb-3 font-semibold">Membres du jury</h2>
                    <ul class="divide-y divide-gray-100">
                        @forelse ($pv->membres as $m)
                            <li class="py-2 text-sm">
                                <span class="font-medium">{{ $m->nom }}</span>
                                @if ($m->fonction) <span class="text-gray-500">— {{ $m->fonction }}</span> @endif
                            </li>
                        @empty
                            <li class="py-2 text-sm text-gray-500">Aucun membre renseigné.</li>
                        @endforelse
                    </ul>
                </div>
            @endif
        </div>

        {{-- Aperçu du scan --}}
        <div class="card lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Aperçu du scan</h2>
                @if ($ext)
                    <a href="{{ route('pvs.fichier', $pv) }}" target="_blank" class="link text-sm">Ouvrir dans un onglet</a>
                @endif
            </div>
            @if ($ext === 'pdf')
                <iframe src="{{ route('pvs.fichier', $pv) }}" class="h-[70vh] w-full rounded-lg border border-slate-200"></iframe>
            @elseif ($ext)
                <img src="{{ route('pvs.fichier', $pv) }}" alt="Scan du PV {{ $pv->reference }}" class="max-h-[70vh] w-full rounded-lg border border-slate-200 object-contain">
            @else
                <p class="rounded-lg bg-slate-50 py-16 text-center text-sm text-slate-500">Aucun fichier numérisé pour ce PV.</p>
            @endif
        </div>
    </div>

    {{-- Résultats --}}
    <div class="card mt-6 p-0" x-data="{ q: '' }">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
            <h2 class="font-semibold">Résultats ({{ $pv->resultats->count() }})</h2>
            <input type="search" x-model="q" placeholder="Filtrer par nom ou matricule…" class="input sm:w-72">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="th">Matricule</th>
                        <th class="th">Nom et prénoms</th>
                        <th class="th text-right">Moyenne</th>
                        <th class="th text-right">Crédits</th>
                        <th class="th">Décision</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pv->resultats as $r)
                        <tr x-show="$el.textContent.toLowerCase().includes(q.toLowerCase())">
                            <td class="td font-mono text-xs">{{ $r->etudiant->matricule }}</td>
                            <td class="td"><a href="{{ route('etudiants.show', $r->etudiant) }}" class="link">{{ $r->etudiant->nom }} {{ $r->etudiant->prenoms }}</a></td>
                            <td class="td text-right">{{ $r->moyenne !== null ? number_format($r->moyenne, 2, ',', ' ') : '—' }}</td>
                            <td class="td text-right">{{ (float) $r->credits_valides }}</td>
                            <td class="td"><x-badge :type="$r->decision" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucun résultat saisi pour ce PV.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
