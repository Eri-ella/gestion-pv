<x-app-layout>
    <x-slot name="header">Tableau de bord</x-slot>

    @php
        $pct = $stats['pv_attendus'] > 0 ? round(100 * $stats['pv_numerises'] / $stats['pv_attendus']) : 0;
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="PV numérisés" :value="$stats['pv_numerises']" :hint="'sur ' . $stats['pv_attendus'] . ' attendus'" />
        <x-stat-card label="PV validés" :value="$stats['pv_total']" />
        <x-stat-card label="Étudiants référencés" :value="number_format($stats['etudiants'], 0, ',', ' ')" />
        <x-stat-card label="Taux de réussite global" :value="$stats['taux_global'] . ' %'" />
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold">Avancement de la numérisation</h2>
            <span class="text-sm text-slate-500">{{ $pct }} %</span>
        </div>
        <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-indigo-600" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-semibold">Taux de réussite par filière et par année</h2>
            <div class="space-y-3">
                @forelse ($tauxParFiliere as $t)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span>{{ $t->filiere }} · {{ $t->annee }}</span>
                            <span class="font-medium">{{ $t->taux }} %</span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $t->taux >= 50 ? 'bg-emerald-500' : 'bg-red-500' }}"
                                style="width: {{ $t->taux }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Aucune donnée pour le moment.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-1 font-semibold">PV manquants</h2>
            <p class="mb-4 text-sm text-slate-500">Promotions sans PV de session normale.</p>
            <ul class="divide-y divide-slate-100">
                @forelse ($pvManquants as $p)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <span>{{ $p->libelle }}</span>
                        @if (auth()->user()->aRole('administrateur', 'agent_scolarite'))
                            <a href="{{ route('pvs.create', ['promotion_id' => $p->id]) }}"
                                class="text-indigo-600 hover:underline">Ajouter</a>
                        @endif
                    </li>
                @empty
                    <li class="py-2 text-sm text-emerald-700">Tous les PV attendus sont présents.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>