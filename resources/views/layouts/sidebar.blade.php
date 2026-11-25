@php
    $u = auth()->user();
    $tous = ['administrateur', 'agent_scolarite', 'direction', 'enseignant'];
    $menu = [
        ['Tableau de bord', 'dashboard', 'dashboard', $tous],
        ['Procès-verbaux', 'pvs.index', 'pvs.*', $tous],
        ['Étudiants', 'etudiants.index', 'etudiants.*', $tous],
        ['Référentiels', 'referentiels.index', 'referentiels.*', ['administrateur']],
        ['Journal', 'journal.index', 'journal.*', ['administrateur', 'direction']],
        ['Utilisateurs', 'utilisateurs.index', 'utilisateurs.*', ['administrateur']],
    ];
@endphp

<aside :class="open ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-300 transition md:static md:translate-x-0">
    <div class="border-b border-slate-700 px-6 py-5">
        <p class="text-lg font-bold text-white">ENEAM</p>
        <p class="text-xs text-slate-400">Gestion des PV de délibération</p>
    </div>
    <nav class="space-y-1 p-3">
        @foreach ($menu as [$label, $route, $actif, $roles])
            @if ($u->aRole(...$roles))
                <a href="{{ route($route) }}"
                    class="block rounded-md px-3 py-2 text-sm font-medium transition
                                  {{ request()->routeIs($actif) ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    {{ $label }}
                </a>
            @endif
        @endforeach
    </nav>
</aside>