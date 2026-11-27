<x-app-layout>
    <x-slot name="header">Tableau de bord</x-slot>

    @php
        $s = $stats;
        $pct = $s['pv_attendus'] > 0 ? round(100 * $s['pv_numerises'] / $s['pv_attendus']) : 0;
        $peutAjouter = auth()->user()->isRole('administrateur', 'agent_scolarite');
    @endphp

    {{-- Cartes de statistiques --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="PV numérisés" :value="$s['pv_numerises']" :hint="'sur ' . $s['pv_attendus'] . ' attendus'"
            icon="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        <x-stat-card label="PV validés" :value="$s['pv_valides']"
            icon="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card label="Étudiants référencés" :value="number_format($s['etudiants'], 0, ',', ' ')"
            icon="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
        <x-stat-card label="Taux de réussite global" :value="$s['taux_global'] . ' %'"
            icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
    </div>

    {{-- Graphiques --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="font-semibold text-slate-900">Taux de réussite par filière et par année</h2>
            <p class="mb-4 text-sm text-slate-500">Étudiants admis / étudiants avec décision</p>
            @if (count($chartTaux['datasets']))
                <div class="h-72"><canvas id="chartTaux"></canvas></div>
            @else
                <p class="py-16 text-center text-sm text-slate-400">Aucun résultat saisi pour le moment.</p>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900">Numérisation des PV</h2>
            <p class="mb-4 text-sm text-slate-500">{{ $pct }} % des PV attendus sont numérisés</p>
            <div class="mx-auto h-56 max-w-xs"><canvas id="chartScan"></canvas></div>
        </div>
    </div>

    {{-- Tableaux --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Derniers PV</h2>
                <a href="{{ route('pvs.index') }}" class="text-sm font-medium text-brand-600 hover:underline">Tout
                    voir</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Référence</th>
                            <th class="px-5 py-3">Promotion</th>
                            <th class="px-5 py-3">Statut</th>
                            <th class="px-5 py-3">Scan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($derniersPv as $pv)
                            <tr>
                                <td class="px-5 py-3 font-medium">
                                    <a href="{{ route('pvs.show', $pv) }}"
                                        class="text-brand-700 hover:underline">{{ $pv->reference }}</a>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $pv->promotion->libelle }}</td>
                                <td class="px-5 py-3"><x-badge-statut :statut="$pv->statut" /></td>
                                <td class="px-5 py-3 text-slate-600">{{ $pv->fichier_path ? 'Oui' : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-400">Aucun PV pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900">PV manquants</h2>
            <p class="mb-3 text-sm text-slate-500">Promotions sans PV.</p>
            <ul class="divide-y divide-slate-100">
                @forelse ($pvManquants as $p)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <span>{{ $p->libelle }}</span>
                        @if ($peutAjouter)
                            <a href="{{ route('pvs.create', ['promotion_id' => $p->id]) }}"
                                class="font-medium text-brand-600 hover:underline">Ajouter</a>
                        @endif
                    </li>
                @empty
                    <li class="py-2 text-sm text-brand-700">Tous les PV attendus sont présents.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const css = getComputedStyle(document.documentElement);
                const c = (name) => css.getPropertyValue(name).trim();
                const palette = [c('--color-brand-600'), c('--color-accent'), '#94a3b8'];

                const taux = @js($chartTaux);
                const elTaux = document.getElementById('chartTaux');
                if (elTaux) {
                    new Chart(elTaux, {
                        type: 'bar',
                        data: {
                            labels: taux.labels,
                            datasets: taux.datasets.map((d, i) => ({
                                ...d,
                                backgroundColor: palette[i % palette.length],
                                borderRadius: 4,
                                maxBarThickness: 36,
                            })),
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { min: 0, max: 100, ticks: { callback: (v) => v + ' %' }, grid: { color: '#f1f5f9' } },
                                x: { grid: { display: false } },
                            },
                            plugins: { legend: { position: 'bottom' } },
                        },
                    });
                }

                new Chart(document.getElementById('chartScan'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Numérisés', 'Manquants'],
                        datasets: [{
                            data: [{{ $s['pv_numerises'] }}, {{ max(0, $s['pv_attendus'] - $s['pv_numerises']) }}],
                            backgroundColor: [c('--color-brand-600'), '#e2e8f0'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { legend: { position: 'bottom' } },
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
