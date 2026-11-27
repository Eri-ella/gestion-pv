@php
    $u = auth()->user();
    // [libellé, route, motifs d'activation, icône (path SVG), rôles autorisés (null = tous), groupe]
    $menu = [
        ['Tableau de bord', 'dashboard', 'dashboard', 'M4 13h6V4H4zM14 20h6V11h-6zM4 20h6v-4H4zM14 7h6V4h-6z', null, 'Principal'],
        ['Procès-verbaux', 'pvs.index', 'pvs.index|pvs.show|pvs.create|pvs.edit', 'M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h6', null, 'Principal'],
        ['Importer des résultats', 'pvs.import', 'pvs.import', 'M12 16V4m0 0l-4 4m4-4l4 4M4 20h16', ['administrateur', 'agent_scolarite'], 'Principal'],
        ['Étudiants', 'etudiants.index', 'etudiants.*', 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0', null, 'Principal'],
        ['Référentiels', 'referentiels.index', 'referentiels.*', 'M12 3l9 5-9 5-9-5zM3 13l9 5 9-5', ['administrateur'], 'Administration'],
        ['Utilisateurs', 'utilisateurs.index', 'utilisateurs.*', 'M16 11a3 3 0 100-6 3 3 0 000 6zM8 12a3 3 0 100-6 3 3 0 000 6zM2 20a6 6 0 0112 0M14 20a6 6 0 018 0', ['administrateur'], 'Administration'],
        ['Journal', 'journal.index', 'journal.*', 'M12 7v5l3 2M12 21a9 9 0 100-18 9 9 0 000 18z', ['administrateur'], 'Administration'],
    ];
    $groupe = null;
@endphp
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gestion des PV') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans text-slate-800 antialiased" x-data="{ open: false }">
    <div class="flex min-h-screen">

        {{-- Menu latéral --}}
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 flex w-64 transform flex-col bg-sidebar text-white transition md:static md:translate-x-0">
            <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
                @if (file_exists(public_path('images/logo-eneam.png')))
                    <img src="{{ asset('images/logo-eneam.png') }}" alt="ENEAM" class="h-10 w-10 rounded bg-white object-contain p-0.5">
                @endif
                <div>
                    <p class="text-lg font-bold leading-tight">ENEAM</p>
                    <p class="text-xs text-white/60">Gestion des PV</p>
                </div>
            </div>
            <div class="flex h-1"><span class="flex-1 bg-primary"></span><span class="flex-1 bg-accent"></span><span class="flex-1 bg-danger"></span><span class="flex-1 bg-info"></span></div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                @foreach ($menu as [$label, $route, $actif, $icone, $roles, $grp])
                    @continue($roles && ! $u->isRole(...$roles))
                    @if ($grp !== $groupe)
                        <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-white/40">{{ $grp }}</p>
                        @php $groupe = $grp; @endphp
                    @endif
                    <a href="{{ route($route) }}"
                        class="flex items-center gap-3 rounded-lg border-l-4 px-3 py-2 text-sm font-medium transition
                        {{ request()->routeIs(...explode('|', $actif)) ? 'border-accent bg-white/10 text-white' : 'border-transparent text-white/70 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $icone }}" /></svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-30 bg-black/40 md:hidden"></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 md:px-8">
                <div class="flex items-center gap-3">
                    <button @click="open = true" class="rounded p-1 text-slate-600 md:hidden" aria-label="Menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <h1 class="text-lg font-semibold">{{ $header ?? '' }}</h1>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-soft font-semibold text-primary-dark">
                        {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                    </span>
                    <span class="hidden text-right leading-tight sm:block">
                        <span class="block font-medium">{{ $u->name }}</span>
                        <span class="block text-xs capitalize text-slate-500">{{ str_replace('_', ' ', $u->role?->libelle) }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-slate-600 hover:bg-slate-50">Déconnexion</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 md:p-8">
                <x-flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
