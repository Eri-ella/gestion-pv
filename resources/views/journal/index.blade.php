<x-app-layout>
    <x-slot:header>Journal d'activité</x-slot:header>

    <form method="GET" class="card grid items-end gap-3 sm:grid-cols-3">
        <div>
            <x-input-label for="action" value="Action" />
            <select id="action" name="action" class="input">
                <option value="">Toutes</option>
                @foreach ($actions as $a)
                    <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="date" value="Date" />
            <x-text-input id="date" type="date" name="date" :value="$date" />
        </div>
        <div class="flex gap-2">
            <x-btn>Filtrer</x-btn>
            <x-btn variant="secondary" :href="route('journal.index')">Réinitialiser</x-btn>
        </div>
    </form>

    <div class="card mt-6 overflow-x-auto p-0">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="th">Date</th>
                    <th class="th">Utilisateur</th>
                    <th class="th">Action</th>
                    <th class="th">Cible</th>
                    <th class="th">Détails</th>
                    <th class="th">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($evenements as $ev)
                    <tr class="hover:bg-slate-50">
                        <td class="td whitespace-nowrap">{{ $ev->created_at->format('d/m/Y H:i') }}</td>
                        <td class="td">{{ $ev->user?->name ?? '—' }}</td>
                        <td class="td"><span class="rounded bg-info-soft px-2 py-0.5 text-xs font-medium text-info">{{ $ev->action }}</span></td>
                        <td class="td">{{ $ev->cible ?? '—' }}</td>
                        <td class="td text-slate-600">{{ $ev->details ?? '—' }}</td>
                        <td class="td font-mono text-xs">{{ $ev->ip ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Aucun événement.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $evenements->links() }}</div>
</x-app-layout>
