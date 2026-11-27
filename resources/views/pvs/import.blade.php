<x-app-layout>
    <x-slot:header>Importer des résultats</x-slot:header>

    <div class="card max-w-2xl">
        <h2 class="mb-1 font-semibold">Fichier CSV des résultats</h2>
        <p class="mb-4 text-sm text-slate-500">Le PV de la promotion choisie est créé automatiquement s'il n'existe pas encore.</p>

        <div class="mb-5 rounded-lg bg-slate-50 p-4 text-xs">
            <p class="mb-1 font-medium">Colonnes attendues (séparateur : virgule) :</p>
            <code class="break-words">matricule, nom, prenoms, moyenne, credits_valides, decision</code>
            <p class="mt-2 text-slate-500">« matricule » et « moyenne » sont obligatoires. « nom » et « prenoms » sont requis pour un nouvel étudiant. Décisions admises : admis, ajourne, rattrapage.</p>
            <p class="mt-1 text-slate-500">Depuis Excel : « Enregistrer sous » → CSV UTF-8 (délimité par des virgules).</p>
        </div>

        <form method="POST" action="{{ route('pvs.import.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="promotion_id" value="Promotion" />
                <select id="promotion_id" name="promotion_id" class="input" required>
                    <option value="">Choisir…</option>
                    @foreach ($promotions as $p)
                        <option value="{{ $p->id }}" @selected(old('promotion_id') == $p->id)>
                            {{ $p->annee->libelle }} · {{ $p->filiere->libelle }} ({{ $p->filiere->code }}) · {{ $p->niveau->libelle }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('promotion_id')" />
            </div>
            <div>
                <x-input-label for="fichier" value="Fichier CSV" />
                <input id="fichier" type="file" name="fichier" accept=".csv,.txt" required
                    class="block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-primary-soft file:px-4 file:py-2 file:font-medium file:text-primary-dark hover:file:bg-primary/20">
                <x-input-error :messages="$errors->get('fichier')" />
            </div>
            <div class="flex gap-2">
                <x-btn>Importer</x-btn>
                <x-btn variant="secondary" :href="route('pvs.index')">Annuler</x-btn>
            </div>
        </form>
    </div>
</x-app-layout>
