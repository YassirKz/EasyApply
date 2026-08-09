<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900 dark:text-white tracking-tight">Historique des e-mails</h1>
                <p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Suivez les candidatures et relances envoyées, avec leurs statuts et dates d'envoi.</p>
            </div>
            <a href="{{ route('entreprises.index') }}" class="px-4 py-2 bg-amber-700 hover:bg-amber-600 text-white rounded-2xl text-sm font-semibold transition">Retour aux entreprises</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:!bg-stone-950 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 overflow-hidden">
                <div class="p-6 bg-stone-50 dark:!bg-stone-950/50 border-b border-stone-200 dark:border-stone-800">
                    <div class="text-xl font-extrabold text-stone-900 dark:text-white">Historique des e-mails</div>
                    <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">Suivez les candidatures et relances envoyées, avec leurs statuts et dates d'envoi.</p>
                </div>

                <div class="p-6 bg-white dark:!bg-stone-950 border-b border-stone-200 dark:border-stone-800">
                    <form method="GET" action="{{ route('historique.index') }}" class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-stone-400 dark:text-stone-400 mb-2">Type</label>
                            <select name="type" class="w-full rounded-2xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm text-stone-900 dark:text-stone-100 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 appearance-none">
                                <option value="">Tous</option>
                                <option value="candidature" {{ request('type') === 'candidature' ? 'selected' : '' }}>Candidature</option>
                                <option value="relance" {{ request('type') === 'relance' ? 'selected' : '' }}>Relance</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-stone-400 dark:text-stone-400 mb-2">Statut</label>
                            <select name="statut" class="w-full rounded-2xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm text-stone-900 dark:text-stone-100 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 appearance-none">
                                <option value="">Tous</option>
                                <option value="envoye" {{ request('statut') === 'envoye' ? 'selected' : '' }}>Envoyé</option>
                                <option value="echec" {{ request('statut') === 'echec' ? 'selected' : '' }}>Échec</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-stone-400 dark:text-stone-400 mb-2">Entreprise</label>
                            <input type="text" name="entreprise" value="{{ request('entreprise') }}" placeholder="Nom entreprise" class="w-full rounded-2xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-sm text-stone-900 dark:text-stone-100 px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        </div>
                        <div class="sm:col-span-3 flex flex-wrap justify-end gap-3 mt-2">
                            <button type="submit" class="px-4 py-3 bg-gradient-to-r from-amber-800 to-amber-700 text-white rounded-2xl text-sm font-semibold transition shadow-md shadow-amber-800/20 hover:from-amber-700 hover:to-amber-600">Filtrer</button>
                            <a href="{{ route('historique.index') }}" class="px-4 py-3 bg-stone-100 dark:bg-stone-800 text-stone-900 dark:text-stone-100 rounded-2xl text-sm font-semibold transition border border-stone-200 dark:border-stone-700 hover:bg-stone-200 dark:hover:bg-stone-700">Réinitialiser</a>
                        </div>
                    </form>
                </div>

                @php
                    $currentSort = request('sort', 'date_envoi');
                    $currentDirection = request('direction', 'desc');
                    $sortDirection = function ($field) use ($currentSort, $currentDirection) {
                        return $currentSort === $field && $currentDirection === 'asc' ? 'desc' : 'asc';
                    };
                    $sortIcon = function ($field) use ($currentSort, $currentDirection) {
                        if ($currentSort !== $field) return '';
                        return $currentDirection === 'asc' ? '↑' : '↓';
                    };
                @endphp
                <div class="overflow-x-auto bg-white dark:!bg-stone-950/95">
                    <table class="min-w-full text-left border-collapse bg-white dark:!bg-stone-950">
                        <thead class="bg-stone-900 text-[11px] uppercase tracking-[0.18em] text-stone-300 border-b border-stone-700">
                            <tr>
                                <th class="px-4 py-4">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'date_envoi', 'direction' => $sortDirection('date_envoi'), 'page' => 1]) }}" class="inline-flex items-center gap-1">
                                        Date {{ $sortIcon('date_envoi') }}
                                    </a>
                                </th>
                                <th class="px-4 py-4">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'entreprise', 'direction' => $sortDirection('entreprise'), 'page' => 1]) }}" class="inline-flex items-center gap-1">
                                        Entreprise {{ $sortIcon('entreprise') }}
                                    </a>
                                </th>
                                <th class="px-4 py-4">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'type', 'direction' => $sortDirection('type'), 'page' => 1]) }}" class="inline-flex items-center gap-1">
                                        Type {{ $sortIcon('type') }}
                                    </a>
                                </th>
                                <th class="px-4 py-4">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'objet', 'direction' => $sortDirection('objet'), 'page' => 1]) }}" class="inline-flex items-center gap-1">
                                        Objet {{ $sortIcon('objet') }}
                                    </a>
                                </th>
                                <th class="px-4 py-4">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'statut', 'direction' => $sortDirection('statut'), 'page' => 1]) }}" class="inline-flex items-center gap-1">
                                        Statut {{ $sortIcon('statut') }}
                                    </a>
                                </th>
                                <th class="px-4 py-4">Contenu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 dark:divide-stone-800 text-sm text-stone-700 dark:text-stone-200">
                            @forelse($historiqueEmails as $historique)
                                <tr class="hover:bg-stone-100 dark:hover:bg-stone-900/80 transition duration-150">
                                    <td class="px-4 py-4 text-xs text-stone-500 dark:text-stone-400">{{ $historique->date_envoi->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-4 font-semibold text-stone-900 dark:text-white">{{ $historique->entreprise?->nom ?? '—' }}</td>
                                    <td class="px-4 py-4 uppercase tracking-[0.12em] text-xs font-bold {{ $historique->type === 'relance' ? 'text-rose-600' : 'text-amber-700' }}">{{ $historique->type }}</td>
                                    <td class="px-4 py-4 font-semibold text-stone-900 dark:text-white">{{ $historique->objet }}</td>
                                    <td class="px-4 py-4"><span class="px-3 py-1 rounded-full text-[11px] font-semibold {{ $historique->statut === 'envoye' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ ucfirst($historique->statut) }}</span></td>
                                    <td class="px-4 py-4 max-w-xl text-xs leading-5 text-stone-600 dark:text-stone-400 whitespace-pre-wrap">{{ Str::limit($historique->contenu, 180) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-stone-500 bg-stone-50 dark:bg-stone-950/60">Aucun historique d'email trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-4 border-t border-stone-800 bg-stone-900/80 dark:bg-stone-950/60">
                    {{ $historiqueEmails->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
