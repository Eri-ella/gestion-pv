@php
    $u = auth()->user();
    // [libellé, route, motif de route actif, rôles autorisés (null = tous), icône]
    $menu = [
        ['Tableau de bord', 'dashboard', 'dashboard', null, 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['Procès-verbaux', 'pvs.index', 'pvs.*', null, 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
        ['Étudiants', 'etudiants.index', 'etudiants.*', null, 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342'],
        ['Référentiels', 'referentiels.index', 'referentiels.*', ['administrateur'], 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
        ['Journal', 'journal.index', 'journal.*', ['administrateur'], 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Utilisateurs', 'utilisateurs.index', 'utilisateurs.*', ['administrateur'], 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
    ];
    $initiales = collect(explode(' ', $u->name))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ENEAM · Gestion des PV</title>
    <link rel="icon" href="{{ asset('images/logo-eneam.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-page font-sans text-ink antialiased" x-data="{ open: false }">

    {{-- Sidebar --}}
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-30 bg-ink/40 lg:hidden"></div>
    <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 flex w-60 flex-col border-r border-line bg-surface transition-transform lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex h-16 items-center gap-3 px-5">
            <img src="{{ asset('images/logo-eneam.png') }}" alt="ENEAM" class="h-10 w-auto">
            <span class="leading-tight"><b class="block tracking-wide">ENEAM</b><span class="text-[11px] text-muted">Gestion des PV</span></span>
        </a>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2">
            @foreach ($menu as [$label, $route, $actif, $roles, $icon])
                @if ($roles === null || $u->isRole(...$roles))
                    @php $on = request()->routeIs($actif); @endphp
                    <a href="{{ route($route) }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $on ? 'bg-primary/10 text-primary' : 'text-ink hover:bg-page' }}">
                        <svg class="h-5 w-5 {{ $on ? 'text-primary' : 'text-muted' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="border-t border-line p-3">
            @csrf
            <button class="w-full rounded-lg px-3 py-2.5 text-left text-sm text-muted hover:bg-page hover:text-ink">Déconnexion</button>
        </form>
    </aside>

    {{-- Contenu --}}
    <div class="flex min-h-screen flex-col lg:ml-60">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-line bg-surface px-4 sm:px-6">
            <button @click="open = true" class="text-muted lg:hidden" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            </button>
            <h1 class="text-lg font-semibold">{{ $header ?? '' }}</h1>
            <div class="ml-auto flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-full bg-primary text-sm font-semibold text-white">{{ $initiales }}</span>
                <span class="hidden leading-tight sm:block">
                    <span class="block text-sm font-semibold">{{ $u->name }}</span>
                    <span class="block text-xs text-muted">{{ ucfirst(str_replace('_', ' ', $u->role?->libelle ?? '')) }}</span>
                </span>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            <x-flash />
            {{ $slot }}
        </main>
    </div>
</body>

</html>
