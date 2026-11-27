<x-app-layout>
    <x-slot:header>Étudiants</x-slot:header>

    <form method="GET" class="flex max-w-xl gap-2">
        <x-text-input name="q" :value="request('q')" placeholder="Nom, prénoms ou matricule" />
        <x-btn>Rechercher</x-btn>
    </form>

    <div class="card mt-6 overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="th">Matricule</th>
                    <th class="th">Nom et prénoms</th>
                    <th class="th">Promotion</th>
                    <th class="th"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($etudiants as $e)
                    <tr class="hover:bg-slate-50">
                        <td class="td font-mono text-xs">{{ $e->matricule }}</td>
                        <td class="td font-medium">{{ $e->nom }} {{ $e->prenoms }}</td>
                        <td class="td text-slate-600">{{ $e->promotion->annee->libelle }} · {{ $e->promotion->filiere->code }} · {{ $e->promotion->niveau->libelle }}</td>
                        <td class="td text-right"><a href="{{ route('etudiants.show', $e) }}" class="link">Voir le parcours</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-500">Aucun étudiant trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $etudiants->links() }}</div>
</x-app-layout>
