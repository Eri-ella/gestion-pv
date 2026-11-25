<x-app-layout>
    <x-slot name="header">Résultats du PV {{ $pv->reference }}</x-slot>

    <div class="grid max-w-5xl gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-1 font-semibold">Importer un fichier</h2>
            <p class="mb-4 text-sm text-slate-500">Fichier Excel (.xlsx) ou CSV avec une ligne d'en-tête.</p>

            <div class="mb-4 overflow-x-auto rounded-md bg-slate-50 p-3 text-xs">
                <p class="mb-1 font-medium">Colonnes attendues :</p>
                <code>matricule ; nom ; prenoms ; moyenne ; credits_valides ; decision</code>
                <p class="mt-2 text-slate-500">Valeurs de decision : admis, ajourne, rattrapage, exclu.</p>
            </div>

            <form method="POST" action="{{ route('pvs.import.store', $pv) }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                <input type="file" name="fichier" accept=".xlsx,.xls,.csv" required
                    class="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-indigo-700 hover:file:bg-indigo-100">
                <x-input-error :messages="$errors->get('fichier')" />
                <x-btn>Importer</x-btn>
            </form>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-semibold">Ajouter un étudiant manuellement</h2>
            <form method="POST" action="{{ route('pvs.resultats.store', $pv) }}" class="grid gap-3 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <x-input-label for="matricule" value="Matricule" />
                    <x-text-input id="matricule" name="matricule" class="mt-1 w-full" :value="old('matricule')"
                        required />
                </div>
                <div>
                    <x-input-label for="nom" value="Nom" />
                    <x-text-input id="nom" name="nom" class="mt-1 w-full" :value="old('nom')" required />
                </div>
                <div>
                    <x-input-label for="prenoms" value="Prénoms" />
                    <x-text-input id="prenoms" name="prenoms" class="mt-1 w-full" :value="old('prenoms')" required />
                </div>
                <div>
                    <x-input-label for="moyenne" value="Moyenne /20" />
                    <x-text-input id="moyenne" type="number" step="0.01" min="0" max="20" name="moyenne"
                        class="mt-1 w-full" :value="old('moyenne')" />
                </div>
                <div>
                    <x-input-label for="credits_valides" value="Crédits validés" />
                    <x-text-input id="credits_valides" type="number" min="0" name="credits_valides" class="mt-1 w-full"
                        :value="old('credits_valides')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="decision" value="Décision" />
                    <x-select id="decision" name="decision" class="mt-1 w-full" required>
                        <option value="admis">Admis</option>
                        <option value="ajourne">Ajourné</option>
                        <option value="rattrapage">Rattrapage</option>
                        <option value="exclu">Exclu</option>
                    </x-select>
                </div>
                <div class="sm:col-span-2"><x-btn>Ajouter</x-btn></div>
            </form>
        </div>
    </div>

    <div class="mt-6"><x-btn variant="secondary" :href="route('pvs.show', $pv)">Retour au PV</x-btn></div>
</x-app-layout>