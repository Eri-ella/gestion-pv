<x-app-layout>
    <x-slot:header>Tableau de bord</x-slot:header>

    @php
        $u = auth()->user();
        $pct = $stats['pv_total'] > 0 ? round(100 * $stats['pv_numerises'] / $stats['pv_total']) : 0;
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card tone="primary" label="Procès-verbaux" :value="$stats['pv_total']" />
        <x-stat-card tone="info" label="PV numérisés" :value="$stats['pv_numerises']" :hint="$stats['pv_attendus'] . ' restant(s) à numériser'" />
        <x-stat-card tone="accent" label="Étudiants référencés" :value="number_format($stats['etudiants'], 0, ',', ' ')" />
        <x-stat-card tone="primary" label="Taux de validation" :value="$stats['taux_global'] . ' %'" hint="PV validés / PV total" />
    </div>

    @if ($u->isRole('administrateur', 'agent_scolarite'))
        <div class="mt-6 flex flex-wrap gap-2">
            <x-btn :href="route('pvs.create')">+ Nouveau PV</x-btn>
            <x-btn variant="secondary" :href="route('pvs.import')">Importer des résultats (CSV)</x-btn>
        </div>
    @endif

    <div class="card mt-6">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold">Avancement de la numérisation</h2>
            <span class="text-sm text-slate-500">{{ $pct }} %</span>
        </div>
        <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-primary" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card">
            <h2 class="mb-4 font-semibold">Taux de validation par filière</h2>
            <div class="space-y-4">
                @forelse ($tauxParFiliere as $t)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span>{{ $t['filiere'] }}</span>
                            <span class="font-medium">{{ $t['taux'] }} %</span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $t['taux'] >= 50 ? 'bg-primary' : 'bg-accent' }}" style="width: {{ $t['taux'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Aucune donnée pour le moment.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h2 class="mb-1 font-semibold">PV sans résultats</h2>
            <p class="mb-4 text-sm text-slate-500">Procès-verbaux dont les résultats n'ont pas encore été saisis.</p>
            <ul class="divide-y divide-slate-100">
                @forelse ($pvManquants as $p)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <span>{{ $p['libelle'] }}</span>
                        <a href="{{ route('pvs.show', $p['id']) }}" class="link">Ouvrir</a>
                    </li>
                @empty
                    <li class="py-2 text-sm text-primary-dark">Tous les PV ont des résultats saisis.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
