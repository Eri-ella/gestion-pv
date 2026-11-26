<x-app-layout>
    <x-slot name="header">{{ $etudiant->nom }} {{ $etudiant->prenoms }}</x-slot>

    @php
        // Même filière + niveau + semestre sur plusieurs années = redoublement
        $cle = fn($r) => $r->pv->promotion->filiere_id . '-' . $r->pv->promotion->niveau_id . '-' . $r->pv->promotion->semestre_id;
        $repetes = $resultats->groupBy($cle)->filter(fn($g) => $g->pluck('pv.promotion.annee_id')->unique()->count() > 1)->keys();
        $total = $resultats->count();
        $admis = $resultats->where('decision', 'admis')->count();
    @endphp

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Matricule" :value="$etudiant->matricule" />
        <x-stat-card label="PV où il apparaît" :value="$total" />
        <x-stat-card label="Décisions « admis »" :value="$admis" :hint="'sur ' . $total" />
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Année</th>
                    <th class="px-4 py-3">Filière</th>
                    <th class="px-4 py-3">Niveau</th>
                    <th class="px-4 py-3">Semestre</th>
                    <th class="px-4 py-3">Session</th>
                    <th class="px-4 py-3 text-right">Moyenne</th>
                    <th class="px-4 py-3">Décision</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($resultats as $r)
                    @php $p = $r->pv->promotion; @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $p->annee->libelle }}</td>
                        <td class="px-4 py-3">{{ $p->filiere->code }}</td>
                        <td class="px-4 py-3">
                            {{ $p->niveau->code }}
                            @if ($repetes->contains($cle($r)))
                                <span
                                    class="ml-1 rounded bg-orange-100 px-1.5 py-0.5 text-xs text-orange-800">Redoublement</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $p->semestre->code }}</td>
                        <td class="px-4 py-3 capitalize">{{ $r->pv->session }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ $r->moyenne !== null ? number_format($r->moyenne, 2, ',', ' ') : '—' }}</td>
                        <td class="px-4 py-3"><x-badge-decision :decision="$r->decision" /></td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('pvs.show', $r->pv) }}"
                                class="text-indigo-600 hover:underline">PV {{ $r->pv->reference }}</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-500">Cet étudiant n'apparaît dans aucun PV.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6"><x-btn variant="secondary" :href="route('etudiants.index')">Retour à la liste</x-btn></div>
</x-app-layout>