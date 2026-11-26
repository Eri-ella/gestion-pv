<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PV {{ $lien->pv->reference }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800 antialiased">
    @php $ext = strtolower(pathinfo($lien->pv->fichier_path, PATHINFO_EXTENSION)); @endphp
    <div class="mx-auto max-w-4xl p-4 md:p-8">
        <div class="mb-4 rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">ENEAM · Procès-verbal de délibération</p>
            <h1 class="mt-1 text-xl font-semibold">{{ $lien->pv->reference }}</h1>
            <p class="text-sm text-slate-600">{{ $lien->pv->promotion->libelle }} · session {{ $lien->pv->session }}</p>
            <p class="mt-2 text-xs text-amber-700">Lien temporaire, valable jusqu'au
                {{ $lien->expire_le->format('d/m/Y à H:i') }}.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            @if ($ext === 'pdf')
                <iframe src="{{ route('partage.fichier', $lien->token) }}"
                    class="h-[75vh] w-full rounded-md border border-slate-200"></iframe>
            @else
                <img src="{{ route('partage.fichier', $lien->token) }}" alt="Scan du PV"
                    class="w-full rounded-md border border-slate-200">
            @endif
        </div>
    </div>
</body>

</html>