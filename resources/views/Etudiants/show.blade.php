<x-app-layout>
    <x-slot:header>{{ $etudiant->nom }} {{ $etudiant->prenoms }}</x-slot:header>

    @php
        $resultats = $etudiant->resultats->filter(fn ($r) => $r->pv);
        $total = $resultats->count();
        $admis = $resultats->where('decision', 'admis')->count();
    @endphp

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card tone="info" label="Matricule" :value="$etudiant->matricule" />
        <x-stat-card tone="accent" label="PV où il apparaît" :value="$total" />
        <x-stat-card tone="primary" label="Décisions « admis »" :value="$admis" :hint="'sur ' . $total" />
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="th">Année</th>
                    <th class="th">Filière</th>
                    <th class="th">Niveau</th>
                    <th class="th text-right">Moyenne</th>
                    <th class="th text-right">Crédits</th>
                    <th class="th">Décision</th>
                    <th class="th"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($resultats as $r)
                    @php $p = $r->pv->promotion; @endphp
                    <tr>
                        <td class="td">{{ $p->annee->libelle }}</td>
                        <td class="td">{{ $p->filiere->code }}</td>
                        <td class="td">{{ $p->niveau->libelle }}</td>
                        <td class="td text-right">{{ $r->moyenne !== null ? number_format($r->moyenne, 2, ',', ' ') : '—' }}</td>
                        <td class="td text-right">{{ (float) $r->credits_valides }}</td>
                        <td class="td"><x-badge :type="$r->decision" /></td>
                        <td class="td text-right"><a href="{{ route('pvs.show', $r->pv) }}" class="link">PV {{ $r->pv->reference }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">Cet étudiant n'apparaît dans aucun PV.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6"><x-btn variant="secondary" :href="route('etudiants.index')">Retour à la liste</x-btn></div>
</x-app-layout>
