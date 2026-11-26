@php $pv = $pv ?? null; @endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($pv) @method('PUT') @endif

    <div class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2">
        <div>
            <x-input-label for="reference" value="Référence" />
            <x-text-input id="reference" name="reference" class="mt-1 w-full" :value="old('reference', $pv?->reference)"
                required />
            <x-input-error :messages="$errors->get('reference')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="promotion_id" value="Promotion" />
            <x-select id="promotion_id" name="promotion_id" class="mt-1 w-full" required>
                <option value="">Choisir…</option>
                @foreach ($promotions as $p)
                    <option value="{{ $p->id }}" @selected(old('promotion_id', $pv?->promotion_id ?? request('promotion_id')) == $p->id)>{{ $p->libelle }}</option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('promotion_id')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="session" value="Session" />
            <x-select id="session" name="session" class="mt-1 w-full" required>
                <option value="normale" @selected(old('session', $pv?->session) === 'normale')>Normale</option>
                <option value="rattrapage" @selected(old('session', $pv?->session) === 'rattrapage')>Rattrapage</option>
            </x-select>
        </div>
        <div>
            <x-input-label for="date_deliberation" value="Date de délibération" />
            <x-text-input id="date_deliberation" type="date" name="date_deliberation" class="mt-1 w-full"
                :value="old('date_deliberation', $pv?->date_deliberation?->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('date_deliberation')" class="mt-1" />
        </div>
        <div class="sm:col-span-2">
            <x-input-label for="president_jury" value="Président du jury" />
            <x-text-input id="president_jury" name="president_jury" class="mt-1 w-full" :value="old('president_jury', $pv?->president_jury)" required />
        </div>
    </div>

    {{-- Membres du jury : ajout et suppression dynamiques --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5"
        x-data="{ membres: @js(old('membres', $pv ? $pv->membres->map(fn($m) => ['nom_complet' => $m->nom_complet, 'fonction' => $m->fonction])->values() : [['nom_complet' => '', 'fonction' => '']])) }">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-semibold">Membres du jury</h2>
            <button type="button" @click="membres.push({ nom_complet: '', fonction: '' })"
                class="text-sm text-indigo-600 hover:underline">+ Ajouter un membre</button>
        </div>
        <div class="space-y-2">
            <template x-for="(m, i) in membres" :key="i">
                <div class="flex gap-2">
                    <input type="text" x-model="m.nom_complet" :name="`membres[${i}][nom_complet]`"
                        placeholder="Nom complet" class="w-full rounded-md border-slate-300 text-sm" required>
                    <input type="text" x-model="m.fonction" :name="`membres[${i}][fonction]`" placeholder="Fonction"
                        class="w-full rounded-md border-slate-300 text-sm">
                    <button type="button" @click="membres.splice(i, 1)" x-show="membres.length > 1"
                        class="px-2 text-red-600">✕</button>
                </div>
            </template>
        </div>
    </div>

    {{-- Scan --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5" x-data="{ nom: '' }">
        <h2 class="mb-3 font-semibold">Fichier scanné</h2>
        @if ($pv)
            <p class="mb-3 text-sm text-slate-500">Un fichier existe déjà. N'en choisis un nouveau que pour le remplacer.
            </p>
        @endif
        <input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png"
            @change="nom = $event.target.files[0]?.name ?? ''" @if (!$pv) required @endif
            class="block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-indigo-700 hover:file:bg-indigo-100">
        <p class="mt-2 text-xs text-slate-500">PDF, JPG ou PNG. <span x-text="nom"></span></p>
        <x-input-error :messages="$errors->get('fichier')" class="mt-1" />
    </div>

    <div class="flex gap-2">
        <x-btn>Enregistrer</x-btn>
        <x-btn variant="secondary" :href="$pv ? route('pvs.show', $pv) : route('pvs.index')">Annuler</x-btn>
    </div>
</form>