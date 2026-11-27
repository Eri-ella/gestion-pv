@php $pv = $pv ?? null; @endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($pv) @method('PUT') @endif

    <div class="card grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="promotion_id" value="Promotion" />
            <select id="promotion_id" name="promotion_id" class="input" required>
                <option value="">Choisir…</option>
                @foreach ($promotions as $p)
                    <option value="{{ $p->id }}" @selected(old('promotion_id', $pv?->promotion_id) == $p->id)>
                        {{ $p->annee->libelle }} · {{ $p->filiere->libelle }} ({{ $p->filiere->code }}) · {{ $p->niveau->libelle }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Une promotion ne peut avoir qu'un seul PV.</p>
            <x-input-error :messages="$errors->get('promotion_id')" />
        </div>
        <div>
            <x-input-label for="reference" value="Référence" />
            <x-text-input id="reference" name="reference" :value="old('reference', $pv?->reference)" required />
            <x-input-error :messages="$errors->get('reference')" />
        </div>
        <div>
            <x-input-label for="date_deliberation" value="Date de délibération" />
            <x-text-input id="date_deliberation" type="date" name="date_deliberation" :value="old('date_deliberation', $pv?->date_deliberation?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('date_deliberation')" />
        </div>
        <div class="sm:col-span-2">
            <x-input-label for="president_jury" value="Président du jury" />
            <x-text-input id="president_jury" name="president_jury" :value="old('president_jury', $pv?->president_jury)" />
            <x-input-error :messages="$errors->get('president_jury')" />
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6"
        x-data="{ membres: @js(old('membres', $pv?->membres->map(fn ($m) => ['nom' => $m->nom, 'fonction' => $m->fonction])->values()->all() ?: [['nom' => '', 'fonction' => '']])) }">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Membres du jury</h2>
            <button type="button" @click="membres.push({ nom: '', fonction: '' })"
                class="text-sm font-medium text-primary hover:underline">+ Ajouter un membre</button>
        </div>

        <div class="space-y-3">
            <template x-for="(m, i) in membres" :key="i">
                <div class="flex items-start gap-2">
                    <input type="text" x-model="m.nom" :name="`membres[${i}][nom]`" placeholder="Nom complet"
                        class="w-full rounded-md border-gray-300 text-sm" required>
                    <input type="text" x-model="m.fonction" :name="`membres[${i}][fonction]`" placeholder="Fonction"
                        class="w-full rounded-md border-gray-300 text-sm">
                    <button type="button" @click="membres.splice(i, 1)" x-show="membres.length > 1"
                        class="px-2 py-1 text-red-600" aria-label="Retirer">✕</button>
                </div>
            </template>
        </div>
        @error('membres.*.nom') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="card" x-data="{ nom: '' }">
        <h2 class="mb-3 font-semibold">Fichier scanné</h2>
        @if ($pv?->fichier_path)
            <p class="mb-3 text-sm text-slate-500">Un fichier existe déjà. N'en choisis un nouveau que pour le remplacer.</p>
        @endif
        <input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png" @change="nom = $event.target.files[0]?.name ?? ''"
            class="block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-primary-soft file:px-4 file:py-2 file:font-medium file:text-primary-dark hover:file:bg-primary/20">
        <p class="mt-2 text-xs text-slate-500">PDF, JPG ou PNG — 10 Mo max. <span x-text="nom" class="font-medium"></span></p>
        <x-input-error :messages="$errors->get('fichier')" />
    </div>

    <div class="flex gap-2">
        <x-btn>Enregistrer</x-btn>
        <x-btn variant="secondary" :href="$pv ? route('pvs.show', $pv) : route('pvs.index')">Annuler</x-btn>
    </div>
</form>
