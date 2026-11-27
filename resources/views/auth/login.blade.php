<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion – {{ config('app.name', 'Gestion des PV') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4 text-slate-800 antialiased">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <img src="{{ asset('images/logo-eneam.png') }}" alt="ENEAM" class="mx-auto mb-4 h-16 w-auto"
                onerror="this.style.display='none'">
            <h1 class="text-xl font-semibold text-slate-900">Gestion des PV de délibération</h1>
            <p class="mt-1 text-sm text-slate-500">École Nationale d'Économie Appliquée et de Management</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="mb-6 text-lg font-semibold text-slate-900">Connexion</h2>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="email" value="Adresse e-mail" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                        autocomplete="username" class="mt-1 w-full" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="password" value="Mot de passe" />
                    <x-text-input id="password" type="password" name="password" required
                        autocomplete="current-password" class="mt-1 w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1"> Se souvenir de moi
                </label>

                <button type="submit"
                    class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                    Se connecter
                </button>
            </form>
        </div>
    </div>
</body>

</html>
