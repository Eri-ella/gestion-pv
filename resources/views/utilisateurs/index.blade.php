<x-app-layout>
    <x-slot name="header">Utilisateurs</x-slot>

    <div x-data="{ form: false }">
        <div class="mb-4 flex justify-end">
            <x-btn type="button" @click="form = !form">Nouvel utilisateur</x-btn>
        </div>

        <form x-show="form" x-cloak method="POST" action="{{ route('utilisateurs.store') }}"
            class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <div><x-input-label for="name" value="Nom" /><x-text-input id="name" name="name" class="mt-1 w-full"
                    required /></div>
            <div><x-input-label for="email" value="E-mail" /><x-text-input id="email" type="email" name="email"
                    class="mt-1 w-full" required /></div>
            <div><x-input-label for="password" value="Mot de passe" /><x-text-input id="password" type="password"
                    name="password" class="mt-1 w-full" required /></div>
            <div>
                <x-input-label for="role_id" value="Rôle" />
                <x-select id="role_id" name="role_id" class="mt-1 w-full" required>
                    @foreach ($roles as $r)
                    <option value="{{ $r->id }}">{{ str_replace('_', ' ', ucfirst($r->libelle)) }}</option> @endforeach
                </x-select>
            </div>
            <div class="sm:col-span-2 lg:col-span-4"><x-btn>Créer le compte</x-btn></div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">E-mail</th>
                    <th class="px-4 py-3">Rôle</th>
                    <th class="px-4 py-3">État</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $u)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $u->role?->libelle) }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $u->actif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $u->actif ? 'Actif' : 'Désactivé' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($u->id !== auth()->id())
                                <form method="POST" action="{{ route('utilisateurs.toggle', $u) }}">
                                    @csrf @method('PATCH')
                                    <button
                                        class="text-indigo-600 hover:underline">{{ $u->actif ? 'Désactiver' : 'Activer' }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>