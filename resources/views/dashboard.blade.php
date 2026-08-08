<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-violet-600 flex items-center justify-center text-white text-lg shadow-xl">📊</span>
                    Tableau de Bord
                </h1>
                <p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Vue d'ensemble des candidatures, des envois et des performances du pipeline.</p>
            </div>
            <a href="{{ route('entreprises.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-800 to-yellow-700 hover:from-amber-700 hover:to-yellow-600 text-white font-bold text-sm rounded-xl shadow-lg transition duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                Voir toutes les entreprises
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 shadow-lg shadow-stone-200/30 dark:shadow-black/20 transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400 dark:text-stone-500">Total entreprises</p>
                            <p class="mt-4 text-3xl font-extrabold text-stone-900 dark:text-white">{{ $total }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-700 dark:text-blue-300 shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-stone-500 dark:text-stone-400">Toutes les entreprises enregistrées dans votre base.</p>
                </div>

                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 shadow-lg shadow-stone-200/30 dark:shadow-black/20 transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400 dark:text-stone-500">En attente</p>
                            <p class="mt-4 text-3xl font-extrabold text-yellow-700 dark:text-yellow-300">{{ $pending }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-yellow-50 dark:bg-yellow-950/50 flex items-center justify-center text-yellow-700 dark:text-yellow-300 shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-stone-500 dark:text-stone-400">Entreprises prêtes à être envoyées ou programmées.</p>
                </div>

                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 shadow-lg shadow-stone-200/30 dark:shadow-black/20 transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400 dark:text-stone-500">Envoyées</p>
                            <p class="mt-4 text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ $sent }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-700 dark:text-emerald-300 shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-stone-500 dark:text-stone-400">Emails envoyés avec succès à vos candidats.</p>
                </div>

                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-6 shadow-lg shadow-stone-200/30 dark:shadow-black/20 transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400 dark:text-stone-500">Taux d'envoi</p>
                            <p class="mt-4 text-3xl font-extrabold text-violet-700 dark:text-violet-300">{{ $sendRate }}%</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-950/50 flex items-center justify-center text-violet-700 dark:text-violet-300 shadow-inner">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m-4 4h8M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-stone-500 dark:text-stone-400">Pourcentage des entreprises transformées en candidatures envoyées.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl shadow-lg shadow-stone-200/30 dark:shadow-black/20 p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.28em] font-semibold text-stone-400 dark:text-stone-500">Envois sur 7 jours</p>
                            <h2 class="mt-3 text-xl font-extrabold text-stone-900 dark:text-white">Tendance récente</h2>
                        </div>
                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 text-xs font-semibold">{{ $lastSevenDays->sum('count') }} envois</span>
                    </div>
                    <div class="mt-6 space-y-4">
                        @foreach($lastSevenDays as $day)
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-medium text-stone-600 dark:text-stone-400">{{ $day['date'] }}</span>
                                <div class="flex-1 h-3 rounded-full bg-stone-100 dark:bg-stone-800 mx-4">
                                    <div class="h-3 rounded-full bg-gradient-to-r from-amber-500 to-yellow-500" style="width: {{ min(100, $day['count'] * 10) }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-stone-900 dark:text-white">{{ $day['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl shadow-lg shadow-stone-200/30 dark:shadow-black/20 p-6">
                    <h2 class="text-base font-extrabold text-stone-900 dark:text-white">Action rapide</h2>
                    <p class="mt-3 text-sm text-stone-500 dark:text-stone-400">Rendez-vous sur les entreprises pour gérer vos envois, importer ou exporter les données.</p>
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('entreprises.index') }}" class="block px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-800 to-yellow-700 text-white font-semibold shadow-lg shadow-amber-800/20 transition hover:brightness-110">Voir toutes les entreprises</a>
                        <a href="{{ route('entreprises.export') }}" class="block px-4 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-violet-600 text-white font-semibold shadow-lg shadow-blue-600/20 transition hover:brightness-110">Exporter CSV</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
