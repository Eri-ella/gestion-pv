<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · ENEAM</title>
    <link rel="icon" href="{{ asset('images/logo-eneam.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="grid min-h-screen place-items-center bg-gradient-to-br from-primary/10 via-page to-secondary/20 p-4 font-sans text-ink antialiased">
    <form method="POST" action="{{ url('/login') }}" class="w-full max-w-sm space-y-4 rounded-2xl border border-line bg-surface p-8 shadow-lg">
        @csrf
        <img src="{{ asset('images/logo-eneam.png') }}" alt="ENEAM" class="mx-auto h-20">
        <div class="text-center">
            <h1 class="text-xl font-semibold">Gestion des PV</h1>
            <p class="text-sm text-muted">Connectez-vous à votre espace</p>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-danger/20 bg-danger/10 px-3 py-2 text-sm text-danger">{{ $errors->first() }}</div>
        @endif

        <label class="block text-sm font-medium">Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="mt-1 w-full rounded-lg border border-line bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none">
        </label>
        <label class="block text-sm font-medium">Mot de passe
            <input type="password" name="password" required autocomplete="current-password"
                class="mt-1 w-full rounded-lg border border-line bg-page px-3 py-2 text-sm focus:border-primary focus:outline-none">
        </label>
        <label class="flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="remember" class="rounded border-line text-primary"> Se souvenir de moi
        </label>
        <button class="w-full rounded-lg bg-primary py-2.5 text-sm font-medium text-white hover:opacity-90">Se connecter</button>
    </form>
</body>

</html>
