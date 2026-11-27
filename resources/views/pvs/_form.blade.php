@php
    $pv = $pv ?? null;

    $membresInit = old('membres', $pv
        ? $pv->membres->map(fn ($m) => ['nom' => $m->nom, 'fonction' => $m->fonction])->values()->all()
        : []);
    if (count($membresInit) === 0) {
        $membresInit = [['nom' => '', 'fonction' => '']];
    }

    $presidentInit = old('president_index');
    if ($presidentInit === null && $pv) {
        $idx = $pv->membres->values()->search(fn ($m) => $m->president);
        $presidentInit = $idx === false ? null : $idx;
    }
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($pv)
        @method('PUT')
    @endif

    <div class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2">
        <div>
            <x-input-label for="reference" value="Référence" />
            <x-text-input id="reference" name="reference" class="mt-1 w-full"
                :value="old('reference', $pv?->reference)" required />
            <x-input-error :messages="$errors->get('reference')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="promotion_id" value="Promotion" />
            <x-select id="promotion_id" name="promotion_id" class="mt-1 w-full" required>
                <option value="">Choisir…</option>
                @foreach ($promotions as $p)
                    <option value="{{ $p->id }}" @selected(old('promotion_id', $pv?->promotion_id ?? request('promotion_id')) == $p->id)>
                        {{ $p->libelle }}</option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('promotion_id')" class="mt-1" />
        </div>
        <div class="sm:col-span-2">
            <x-input-label for="date_deliberation" value="Date de délibération" />
            <x-text-input id="date_deliberation" type="date" name="date_deliberation" class="mt-1 w-full sm:w-1/2"
                :value="old('date_deliberation', $pv?->date_deliberation?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('date_deliberation')" class="mt-1" />
        </div>
    </div>

    {{-- Membres du jury : ajout / suppression dynamiques, un seul président --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        x-data="{
            membres: @js($membresInit),
            president: @js($presidentInit === null ? null : (int) $presidentInit),
            ajouter() { this.membres.push({ nom: '', fonction: '' }); },
            retirer(i) {
                this.membres.splice(i, 1);
                if (this.president === i) { this.president = null; }
                else if (this.president !== null && this.president > i) { this.president--; }
            },
        }">
        <div class="mb-1 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900">Membres du jury</h2>
            <button type="button" @click="ajouter()"
                class="text-sm font-medium text-brand-600 hover:underline">+ Ajouter un membre</button>
        </div>
        <p class="mb-4 text-sm text-slate-500">Cochez « Président » pour désigner le président du jury.</p>

        <div class="space-y-2">
            <template x-for="(m, i) in membres" :key="i">
                <div class="flex flex-wrap items-center gap-2 sm:flex-nowrap">
                    <input type="text" x-model="m.nom" :name="`membres[${i}][nom]`" placeholder="Nom complet"
                        class="w-full" required>
                    <input type="text" x-model="m.fonction" :name="`membres[${i}][fonction]`" placeholder="Fonction"
                        class="w-full">
                    <label class="flex shrink-0 items-center gap-1 text-sm text-slate-600">
                        <input type="radio" name="president_index" :value="i" x-model.number="president"> Président
                    </label>
                    <button type="button" @click="retirer(i)" x-show="membres.length > 1"
                        class="px-2 text-lg leading-none text-slate-400 hover:text-red-600"
                        aria-label="Retirer ce membre">&times;</button>
                </div>
            </template>
        </div>
        <x-input-error :messages="$errors->get('membres')" class="mt-2" />
    </div>

    {{-- Scan --}}
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" x-data="{ nom: '' }">
        <h2 class="mb-3 font-semibold text-slate-900">Fichier scanné</h2>
        @if ($pv)
            <p class="mb-3 text-sm text-slate-500">Un fichier existe déjà. N'en choisissez un nouveau que pour le
                remplacer.</p>
        @endif
        <input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png"
            @change="nom = $event.target.files[0]?.name ?? ''"
            class="block w-full text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-brand-700 hover:file:bg-brand-100">
        <p class="mt-2 text-xs text-slate-500">PDF, JPG ou PNG, 10 Mo maximum. <span x-text="nom"></span></p>
        <x-input-error :messages="$errors->get('fichier')" class="mt-1" />
    </div>

    <div class="flex gap-2">
        <x-btn>Enregistrer</x-btn>
        <x-btn variant="secondary" :href="$pv ? route('pvs.show', $pv) : route('pvs.index')">Annuler</x-btn>
    </div>
</form>
