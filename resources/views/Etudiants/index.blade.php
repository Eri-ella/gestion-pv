<x-app-layout>
    <x-slot name="header">Étudiants</x-slot>

    <form method="GET" class="flex max-w-xl gap-2">
        <x-text-input name="q" class="w-full" :value="request('q')" placeholder="Nom, prénoms ou matricule" />
        <x-btn>Rechercher</x-btn>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Matricule</th>
                    <th class="px-4 py-3">Nom et prénoms</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($etudiants as $e)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $e->matricule }}</td>
                        <td class="px-4 py-3">{{ $e->nom }} {{ $e->prenoms }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('etudiants.show', $e) }}"
                                class="text-indigo-600 hover:underline">Voir le parcours</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-slate-500">Aucun étudiant trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $etudiants->withQueryString()->links() }}</div>
</x-app-layout>