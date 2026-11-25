<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('GESTION -PV', 'Gestion des PV') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ open: false }">
    <div class="flex min-h-screen">

        @include('layouts.sidebar')

        {{-- Fond sombre mobile --}}
        <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-30 bg-black/40 md:hidden"></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 md:px-8">
                <div class="flex items-center gap-3">
                    <button @click="open = true" class="rounded p-1 text-slate-600 md:hidden" aria-label="Menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold">{{ $header ?? '' }}</h1>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="hidden text-right sm:block">
                        <span class="block font-medium">{{ auth()->user()->name }}</span>
                        <span
                            class="block text-xs text-slate-500">{{ str_replace('_', ' ', auth()->user()->role?->libelle) }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-600 hover:bg-slate-50">Déconnexion</button>
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