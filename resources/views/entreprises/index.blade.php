<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-800 to-amber-700 flex items-center justify-center text-white text-sm shadow-md shadow-amber-800/20">🏢</span>
                    Gestion des Entreprises Allemandes
                </h1>
                <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-1">
                    Gérez vos prospects, personnalisez vos courriels avec l'IA Gemini et automatisez l'envoi direct.
                </p>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex items-center gap-3 flex-wrap w-full sm:w-auto">
                <!-- Test Email CTA -->
                <form action="{{ route('envoi.test') }}" method="POST" onsubmit="return confirm('Envoyer un email test à kezziyassir005@gmail.com pour prévisualiser le rendu ?');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-200 font-semibold text-xs sm:text-sm rounded-xl shadow-sm hover:shadow transition duration-200">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>📨 Test Email</span>
                    </button>
                </form>

                <!-- Schedule CTA (Triggers global event so Alpine inside main slot receives it) -->
                <button type="button" 
                        onclick="window.dispatchEvent(new CustomEvent('open-schedule-modal'))"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-700 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-amber-700/20 hover:scale-[1.02] active:scale-[0.98] transition duration-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Programmer</span>
                </button>

                <!-- Mass Send CTA -->
                <form action="{{ route('envoi.masse') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir envoyer les candidatures aux {{ $pendingCount }} entreprise(s) en attente ?');">
                    @csrf
                    <button type="submit" 
                            class="relative group inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-800 via-amber-700 to-yellow-700 hover:from-amber-700 hover:to-yellow-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-amber-800/25 hover:shadow-amber-700/40 hover:scale-[1.02] active:scale-[0.98] transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                            {{ $pendingCount == 0 ? 'disabled' : '' }}>
                        <svg class="w-4 h-4 {{ $pendingCount > 0 ? 'animate-bounce' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span>🚀 Lancer {{ $pendingCount > 0 ? "({$pendingCount})" : "(0)" }}</span>
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 min-h-screen" 
         x-data="{ 
            addModal: false, 
            importModal: false, 
            editModal: false, 
            scheduleModal: false,
            editCompany: {}, 
            selectedIds: [], 
            pageIds: {{ json_encode($entreprises->pluck('id')) }},
            _companiesCache: {},
            toggleSelectAll(e) {
                if (e.target.checked) {
                    this.selectedIds = [...this.pageIds];
                } else {
                    this.selectedIds = [];
                }
            }
         }"
         @open-schedule-modal.window="scheduleModal = true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 📊 4 STATISTICAL DASHBOARD CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Companies -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm hover:shadow-md transition duration-200 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 dark:bg-amber-500/10 rounded-full group-hover:scale-125 transition duration-300"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider">Total Entreprises</p>
                            <h3 class="text-2xl font-black text-stone-900 dark:text-white mt-1">{{ $entreprises->total() }}</h3>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 flex items-center justify-center font-bold shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-xs text-stone-500 dark:text-stone-400">
                        <span class="w-2 h-2 rounded-full bg-amber-700"></span> Base de prospects cibles
                    </div>
                </div>

                <!-- Card 2: En Attente -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm hover:shadow-md transition duration-200 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-yellow-500/5 dark:bg-yellow-500/10 rounded-full group-hover:scale-125 transition duration-300"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider">En Attente</p>
                            <h3 class="text-2xl font-black text-yellow-700 dark:text-yellow-400 mt-1">{{ $pendingCount }}</h3>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-yellow-50 dark:bg-yellow-950/60 text-yellow-700 dark:text-yellow-400 flex items-center justify-center font-bold shadow-inner relative">
                            @if($pendingCount > 0)
                                <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-yellow-500 animate-ping"></span>
                            @endif
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-xs text-stone-500 dark:text-stone-400">
                        <span class="w-2 h-2 rounded-full bg-yellow-600"></span> Prêtes à recevoir l'email
                    </div>
                </div>

                <!-- Card 3: Envoyées -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm hover:shadow-md transition duration-200 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-full group-hover:scale-125 transition duration-300"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider">Envoyées</p>
                            <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $sentCount }}</h3>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-xs text-stone-500 dark:text-stone-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Candidatures expédiées
                    </div>
                </div>

                <!-- Card 4: Programmées -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl p-5 border border-stone-200/80 dark:border-stone-800 shadow-sm hover:shadow-md transition duration-200 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-800/5 dark:bg-amber-800/10 rounded-full group-hover:scale-125 transition duration-300"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-stone-400 dark:text-stone-500 uppercase tracking-wider">Programmées</p>
                            <h3 class="text-2xl font-black text-amber-800 dark:text-amber-400 mt-1">{{ $scheduledCount }}</h3>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 flex items-center justify-center font-bold shadow-inner relative">
                            @if($scheduledCount > 0)
                                <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                            @endif
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-xs text-stone-500 dark:text-stone-400">
                        <span class="w-2 h-2 rounded-full bg-amber-700"></span> Envoi automatique planifié
                    </div>
                </div>
            </div>

            <!-- Toolbar & Controls Bar -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 overflow-hidden">
                <div class="p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    
                    <!-- Search Bar -->
                    <form method="GET" action="{{ route('entreprises.index') }}" class="flex items-center gap-2 w-full md:max-w-md">
                        <div class="relative flex-1">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 dark:text-stone-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Rechercher par nom, email, directeur..." 
                                   class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-stone-50 dark:bg-stone-800/80 border border-stone-200 dark:border-stone-700/80 rounded-xl focus:ring-2 focus:ring-amber-500 dark:focus:ring-amber-400 focus:bg-white dark:focus:bg-stone-800 text-stone-800 dark:text-stone-100 placeholder-stone-400 transition duration-150">
                        </div>
                        <button type="submit" class="px-4 py-2.5 bg-stone-900 dark:bg-stone-700 hover:bg-stone-800 dark:hover:bg-stone-600 text-white font-semibold text-xs sm:text-sm rounded-xl transition duration-150">
                            Filtrer
                        </button>
                        @if(request('search'))
                            <a href="{{ route('entreprises.index') }}" class="px-3 py-2.5 bg-stone-100 dark:bg-stone-800 text-stone-500 hover:text-stone-700 dark:hover:text-stone-300 font-bold text-xs sm:text-sm rounded-xl transition duration-150">✕</a>
                        @endif
                    </form>

                    <!-- Quick Actions Row -->
                    <div class="flex items-center gap-2.5 flex-wrap justify-end">
                        <!-- Batch Delete Button -->
                        <form action="{{ route('entreprises.destroyBatch') }}" method="POST" x-show="selectedIds.length > 0" x-cloak onsubmit="return confirm('Supprimer définitivement la sélection ?');">
                            @csrf
                            @method('DELETE')
                            <template x-for="id in selectedIds" :key="id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                            <button type="submit" class="px-3.5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm animate-pulse transition duration-150 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Supprimer (<span x-text="selectedIds.length"></span>)</span>
                            </button>
                        </form>

                        <!-- Tout Vider -->
                        @if($entreprises->total() > 0)
                        <form action="{{ route('entreprises.destroyAll') }}" method="POST" onsubmit="return confirm('⚠️ Êtes-vous ABSOLUMENT sûr de vouloir TOUT supprimer ? Cette action est irréversible.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2.5 bg-stone-100 dark:bg-stone-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-stone-500 dark:text-stone-400 hover:text-rose-600 dark:hover:text-rose-400 font-semibold text-xs sm:text-sm rounded-xl border border-stone-200 dark:border-stone-700 transition" title="Tout supprimer">
                                🗑️ Vider
                            </button>
                        </form>
                        @endif

                        <!-- Programmer -->
                        <button @click="scheduleModal = true" type="button" class="px-4 py-2.5 bg-amber-700 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-amber-700/20 transition duration-200 flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Programmer</span>
                        </button>

                        <!-- Générer Tout IA -->
                        <form action="{{ route('entreprises.geminiAll') }}" method="POST" onsubmit="return confirm('Générer automatiquement le texte IA pour TOUTES les entreprises ?');">
                            @csrf
                            <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-amber-800 to-amber-700 hover:from-amber-700 hover:to-amber-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-amber-800/20 transition duration-200 flex items-center gap-1.5">
                                <span>✨</span>
                                <span>Générer IA (Tout)</span>
                            </button>
                        </form>

                        <!-- Nouvelle Entreprise -->
                        <button @click="addModal = true" class="px-4 py-2.5 bg-amber-800 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-amber-800/20 transition duration-200 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Nouvelle</span>
                        </button>

                        <!-- Import -->
                        <button @click="importModal = true" class="px-4 py-2.5 bg-stone-800 dark:bg-stone-700 hover:bg-stone-700 dark:hover:bg-stone-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm transition duration-200 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Import CSV</span>
                        </button>
                    </div>

                </div>
            </div>

            <!-- 📋 ENTREPRISES TABLE -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 overflow-hidden transition-colors duration-300">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-stone-50/80 dark:bg-stone-800/60 border-b border-stone-200/80 dark:border-stone-800 text-[11px] font-bold text-stone-400 dark:text-stone-400 uppercase tracking-wider">
                                <th class="py-4 px-4 w-10 text-center">
                                    <input type="checkbox" 
                                           @change="toggleSelectAll($event)" 
                                           :checked="selectedIds.length > 0 && selectedIds.length === pageIds.length"
                                           class="rounded border-stone-300 dark:border-stone-700 text-amber-700 focus:ring-amber-500 dark:bg-stone-800 cursor-pointer">
                                </th>
                                <th class="py-4 px-6">Entreprise</th>
                                <th class="py-4 px-6">Contact / Email</th>
                                <th class="py-4 px-6">Secteur</th>
                                <th class="py-4 px-6">Texte Personnalisé (IA)</th>
                                <th class="py-4 px-6">Statut</th>
                                <th class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-stone-800 text-sm font-medium text-stone-700 dark:text-stone-300">
                            @forelse($entreprises as $entreprise)
                                <tr class="group hover:bg-amber-50/40 dark:hover:bg-stone-800/50 transition duration-150" :class="{'bg-amber-50/60 dark:bg-stone-800/80': selectedIds.includes({{ $entreprise->id }})}">
                                    <td class="py-4 px-4 text-center">
                                        <input type="checkbox" 
                                               value="{{ $entreprise->id }}" 
                                               x-model.number="selectedIds" 
                                               class="rounded border-stone-300 dark:border-stone-700 text-amber-700 focus:ring-amber-500 dark:bg-stone-800 cursor-pointer">
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-stone-900 dark:text-white group-hover:text-amber-800 dark:group-hover:text-amber-400 transition duration-150">
                                            {{ $entreprise->nom }}
                                        </div>
                                        @if($entreprise->telephone)
                                            <div class="text-xs text-stone-400 dark:text-stone-500 mt-0.5 flex items-center gap-1">
                                                <span>📞</span> {{ $entreprise->telephone }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="text-amber-800 dark:text-amber-400 font-semibold text-xs sm:text-sm">{{ $entreprise->email }}</div>
                                        <div class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">👤 {{ $entreprise->directeur }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-block px-2.5 py-1 bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 text-xs font-semibold rounded-lg border border-stone-200/80 dark:border-stone-700/80">
                                            {{ $entreprise->secteur ?? 'Non spécifié' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 max-w-xs">
                                        <div id="ai-text-{{ $entreprise->id }}" class="text-xs text-stone-600 dark:text-stone-300 line-clamp-3 bg-stone-50 dark:bg-stone-800/60 p-2.5 rounded-xl border border-stone-200/80 dark:border-stone-700/80 mb-2 leading-relaxed">
                                            {{ $entreprise->texte_personnalise ?: 'Aucun texte généré' }}
                                        </div>
                                        <button type="button" 
                                                onclick="generateGeminiText({{ $entreprise->id }}, this)"
                                                class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-amber-800 dark:text-amber-300 font-bold rounded-lg border border-amber-200 dark:border-amber-800/60 transition duration-150">
                                            <span>✨</span> IA Gemini
                                        </button>
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if($entreprise->est_envoye)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-xs font-bold rounded-full border border-emerald-200 dark:border-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Envoyé {{ $entreprise->date_envoi ? $entreprise->date_envoi->format('d/m/Y') : '' }}
                                            </span>
                                        @elseif($entreprise->programmation_envoi)
                                            <div class="flex items-center gap-1.5">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 text-xs font-bold rounded-full border border-amber-200 dark:border-amber-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> 🗓️ {{ $entreprise->programmation_envoi->format('d/m H:i') }}
                                                </span>
                                                <form action="{{ route('envoi.annulerProgrammation', $entreprise) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-bold px-1" title="Annuler la programmation">✕</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-50 dark:bg-yellow-950/50 text-yellow-700 dark:text-yellow-300 text-xs font-bold rounded-full border border-yellow-200 dark:border-yellow-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-ping"></span> En attente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Edit Button -->
                                            <button 
                                                type="button"
                                                @click="editCompany = {
                                                    id: {{ $entreprise->id }},
                                                    nom: {{ Js::from(htmlspecialchars_decode($entreprise->nom ?? '', ENT_QUOTES)) }},
                                                    email: {{ Js::from($entreprise->email ?? '') }},
                                                    directeur: {{ Js::from(htmlspecialchars_decode($entreprise->directeur ?? '', ENT_QUOTES)) }},
                                                    telephone: {{ Js::from(htmlspecialchars_decode($entreprise->telephone ?? '', ENT_QUOTES)) }},
                                                    secteur: {{ Js::from(htmlspecialchars_decode($entreprise->secteur ?? '', ENT_QUOTES)) }},
                                                    texte_personnalise: (window._companiesCache && window._companiesCache[{{ $entreprise->id }}] !== undefined)
                                                        ? window._companiesCache[{{ $entreprise->id }}]
                                                        : {{ Js::from(htmlspecialchars_decode($entreprise->texte_personnalise ?? '', ENT_QUOTES)) }},
                                                    est_envoye: {{ $entreprise->est_envoye ? 'true' : 'false' }}
                                                }; editModal = true"
                                                class="p-2 text-stone-400 hover:text-amber-800 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-stone-800 rounded-xl transition duration-150" 
                                                title="Modifier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>

                                            <!-- Direct Delete Button -->
                                            <form action="{{ route('entreprises.destroy', $entreprise) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-stone-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-stone-800 rounded-xl transition duration-150" title="Supprimer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-stone-400 dark:text-stone-500">
                                        <div class="max-w-xs mx-auto text-center space-y-2">
                                            <div class="text-3xl">🔍</div>
                                            <p class="font-bold text-stone-600 dark:text-stone-300">Aucune entreprise enregistrée</p>
                                            <p class="text-xs">Utilisez les boutons ci-dessus pour ajouter ou importer des entreprises.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-stone-100 dark:border-stone-800">
                    {{ $entreprises->links() }}
                </div>
            </div>

        </div>

        <!-- ➕ ADD COMPANY MODAL -->
        <div x-show="addModal" x-cloak class="fixed inset-0 bg-stone-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div @click.away="addModal = false" class="bg-white dark:bg-stone-900 rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center border-b border-stone-100 dark:border-stone-800 pb-4">
                    <h3 class="font-extrabold text-lg text-stone-900 dark:text-white flex items-center gap-2">
                        <span>🏢</span> Ajouter une Entreprise
                    </h3>
                    <button @click="addModal = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 font-bold text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('entreprises.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Nom de l'entreprise *</label>
                        <input type="text" name="nom" required placeholder="ex: Bosch GmbH" class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Email Destinataire *</label>
                            <input type="email" name="email" required placeholder="karriere@bosch.de" class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Nom RH / Directeur *</label>
                            <input type="text" name="directeur" required placeholder="ex: Herr Schmidt" class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Téléphone</label>
                            <input type="text" name="telephone" placeholder="+49..." class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Secteur</label>
                            <input type="text" name="secteur" placeholder="ex: Automotive IT" class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Texte Personnalisé (Optionnel)</label>
                        <textarea name="texte_personnalise" rows="3" placeholder="Saisissez un texte ou laissez Gemini IA le rédiger..." class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100 dark:border-stone-800">
                        <button type="button" @click="addModal = false" class="px-4 py-2 bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 text-xs font-bold rounded-xl hover:bg-stone-200 transition">Annuler</button>
                        <button type="submit" class="px-5 py-2 bg-amber-800 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-md transition">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ✏️ EDIT COMPANY MODAL -->
        <div x-show="editModal" x-cloak class="fixed inset-0 bg-stone-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div @click.away="editModal = false" class="bg-white dark:bg-stone-900 rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-5 border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center border-b border-stone-100 dark:border-stone-800 pb-4">
                    <h3 class="font-extrabold text-lg text-stone-900 dark:text-white flex items-center gap-2">
                        <span>✏️</span> Modifier l'Entreprise
                    </h3>
                    <button @click="editModal = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 font-bold text-xl leading-none">&times;</button>
                </div>
                <form :action="'/entreprises/' + editCompany.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Nom de l'entreprise *</label>
                        <input type="text" name="nom" x-model="editCompany.nom" required class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Email Destinataire *</label>
                            <input type="email" name="email" x-model="editCompany.email" required class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Nom RH / Directeur *</label>
                            <input type="text" name="directeur" x-model="editCompany.directeur" required class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Téléphone</label>
                            <input type="text" name="telephone" x-model="editCompany.telephone" class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Secteur</label>
                            <input type="text" name="secteur" x-model="editCompany.secteur" class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Texte Personnalisé</label>
                        <textarea name="texte_personnalise" x-model="editCompany.texte_personnalise" rows="3" class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100 dark:border-stone-800">
                        <button type="button" @click="editModal = false" class="px-4 py-2 bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 text-xs font-bold rounded-xl hover:bg-stone-200 transition">Annuler</button>
                        <button type="submit" class="px-5 py-2 bg-amber-800 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-md transition">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 📥 IMPORT EXCEL/CSV MODAL -->
        <div x-show="importModal" x-cloak class="fixed inset-0 bg-stone-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div @click.away="importModal = false" class="bg-white dark:bg-stone-900 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-5 border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center border-b border-stone-100 dark:border-stone-800 pb-4">
                    <h3 class="font-extrabold text-lg text-stone-900 dark:text-white flex items-center gap-2">
                        <span>📥</span> Import Fichier CSV / Excel
                    </h3>
                    <button @click="importModal = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 font-bold text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('entreprises.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="p-4 bg-amber-50/60 dark:bg-amber-950/40 border border-amber-100 dark:border-amber-900/50 rounded-xl text-xs text-amber-900 dark:text-amber-300 leading-relaxed">
                        <p class="font-bold mb-1">📋 Entêtes automatiquement supportées :</p>
                        <code class="bg-white dark:bg-stone-800 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800 font-mono font-bold block mt-1 text-amber-800 dark:text-amber-300">FULLNAME | PHONE | E-MAIL | GMBH NAME</code>
                        <p class="mt-2">Ou en français : <code class="bg-white dark:bg-stone-800 px-1 py-0.5 rounded border border-amber-200 dark:border-amber-800 font-mono">nom, email, directeur, telephone, secteur</code></p>
                    </div>

                    <div>
                        <input type="file" name="file" required accept=".csv, .xlsx, .xls" class="w-full text-xs text-stone-500 dark:text-stone-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 dark:file:bg-stone-800 dark:file:text-amber-400 border border-stone-200 dark:border-stone-700 rounded-xl p-1.5 cursor-pointer">
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100 dark:border-stone-800">
                        <button type="button" @click="importModal = false" class="px-4 py-2 bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 text-xs font-bold rounded-xl">Annuler</button>
                        <button type="submit" class="px-5 py-2 bg-stone-900 dark:bg-stone-700 hover:bg-stone-800 text-white text-xs font-bold rounded-xl shadow-md">Lancer l'import</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 🗓️ SCHEDULE DISPATCH MODAL -->
        <div x-show="scheduleModal" x-cloak class="fixed inset-0 bg-stone-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div @click.away="scheduleModal = false" class="bg-white dark:bg-stone-900 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-5 border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center border-b border-stone-100 dark:border-stone-800 pb-4">
                    <h3 class="font-extrabold text-lg text-stone-900 dark:text-white flex items-center gap-2">
                        <span>🗓️</span> Programmer l'Envoi Automatique
                    </h3>
                    <button @click="scheduleModal = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 font-bold text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('envoi.programmer') }}" method="POST" class="space-y-4">
                    @csrf
                    <p class="text-xs text-stone-500 dark:text-stone-400 leading-relaxed">
                        Choisissez la date et l'heure exactes auxquelles l'application doit envoyer automatiquement les candidatures aux <strong class="text-stone-900 dark:text-white">{{ $pendingCount }} entreprise(s) en attente</strong>.
                    </p>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1.5">Date & Heure d'Envoi *</label>
                        <input type="datetime-local" 
                               name="programmation_envoi" 
                               required 
                               min="{{ date('Y-m-d\TH:i') }}"
                               value="{{ date('Y-m-d\T09:00', strtotime('+1 day')) }}"
                               class="w-full text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white">
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100 dark:border-stone-800">
                        <button type="button" @click="scheduleModal = false" class="px-4 py-2 bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 text-xs font-bold rounded-xl hover:bg-stone-200 transition">Annuler</button>
                        <button type="submit" class="px-5 py-2 bg-amber-800 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-md transition">Valider la Programmation</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Gemini AI AJAX Script -->
    <script>
        window._companiesCache = window._companiesCache || {};

        function generateGeminiText(entrepriseId, btn) {
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '⌛ IA...';

            fetch(`/entreprises/${entrepriseId}/gemini`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Server error: ' + res.status);
                return res.json();
            })
            .then(data => {
                btn.disabled = false;

                if (data.success) {
                    const textDiv = document.getElementById(`ai-text-${entrepriseId}`);
                    if (textDiv) {
                        textDiv.innerText = data.texte_personnalise;
                        textDiv.classList.add('bg-amber-100/50', 'border-amber-300', 'dark:bg-amber-950/50');
                    }

                    window._companiesCache[entrepriseId] = data.texte_personnalise;

                    btn.innerHTML = '✅ Généré !';
                    btn.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-300');
                    setTimeout(() => {
                        btn.innerHTML = originalContent;
                        btn.classList.remove('bg-emerald-100', 'text-emerald-700', 'border-emerald-300');
                    }, 2500);
                } else {
                    btn.innerHTML = originalContent;
                    alert('Erreur lors de la génération IA.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                console.error('Gemini error:', err);
                alert('Erreur réseau lors de la génération.');
            });
        }
    </script>
</x-app-layout>
