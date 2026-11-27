<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · {{ config('app.name', 'Gestion des PV') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex h-1.5"><span class="flex-1 bg-primary"></span><span class="flex-1 bg-accent"></span><span class="flex-1 bg-danger"></span><span class="flex-1 bg-info"></span></div>
            <div class="p-8">
                <div class="text-center">
                    @if (file_exists(public_path('images/logo-eneam.png')))
                        <img src="{{ asset('images/logo-eneam.png') }}" alt="ENEAM" class="mx-auto mb-3 h-16">
                    @endif
                    <h1 class="text-2xl font-bold text-primary-dark">ENEAM</h1>
                    <p class="text-sm text-slate-500">Gestion des procès-verbaux de délibération</p>
                </div>

                <form method="POST" action="{{ url('/login') }}" class="mt-8 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="email" value="Adresse e-mail" />
                        <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>
                    <div>
                        <x-input-label for="password" value="Mot de passe" />
                        <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-primary focus:ring-primary"> Se souvenir de moi
                    </label>
                    <x-btn class="w-full">Se connecter</x-btn>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
