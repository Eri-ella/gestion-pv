<x-app-layout>
    <x-slot:header>Utilisateurs</x-slot:header>

    <div x-data="{ form: {{ $errors->any() ? 'true' : 'false' }}, role: '{{ old('role_id') }}', chefs: @js($roles->where('libelle', 'chef_departement')->pluck('id')->map(fn ($id) => (string) $id)->values()) }">
        <div class="mb-4 flex justify-end">
            <x-btn type="button" @click="form = !form">+ Nouvel utilisateur</x-btn>
        </div>

        <form x-show="form" x-cloak method="POST" action="{{ route('utilisateurs.store') }}" class="card mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <div>
                <x-input-label for="name" value="Nom" />
                <x-text-input id="name" name="name" :value="old('name')" required />
            </div>
            <div>
                <x-input-label for="email" value="E-mail" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required />
            </div>
            <div>
                <x-input-label for="password" value="Mot de passe (8 caractères min.)" />
                <x-text-input id="password" type="password" name="password" required />
            </div>
            <div>
                <x-input-label for="role_id" value="Rôle" />
                <select id="role_id" name="role_id" x-model="role" class="input" required>
                    <option value="">Choisir…</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->id }}">{{ ucfirst(str_replace('_', ' ', $r->libelle)) }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="chefs.includes(role)" x-cloak>
                <x-input-label for="departement_id" value="Département (chef de département)" />
                <select id="departement_id" name="departement_id" class="input">
                    <option value="">Choisir…</option>
                    @foreach ($departements as $d)
                        <option value="{{ $d->id }}" @selected(old('departement_id') == $d->id)>{{ $d->code }} — {{ $d->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end"><x-btn>Créer le compte</x-btn></div>
        </form>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="th">Nom</th>
                    <th class="th">E-mail</th>
                    <th class="th">Rôle</th>
                    <th class="th">Département</th>
                    <th class="th">État</th>
                    <th class="th"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $u)
                    <tr>
                        <td class="td font-medium">{{ $u->name }}</td>
                        <td class="td">{{ $u->email }}</td>
                        <td class="td capitalize">{{ str_replace('_', ' ', $u->role?->libelle) }}</td>
                        <td class="td">{{ $u->departement?->code ?? '—' }}</td>
                        <td class="td">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $u->actif ? 'bg-primary-soft text-primary-dark' : 'bg-slate-200 text-slate-600' }}">{{ $u->actif ? 'Actif' : 'Désactivé' }}</span>
                        </td>
                        <td class="td text-right">
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('utilisateurs.toggle', $u) }}">
                                    @csrf
                                    <button class="link">{{ $u->actif ? 'Désactiver' : 'Activer' }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-app-layout>
