<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-800 to-amber-700 flex items-center justify-center text-white text-sm shadow-md shadow-amber-800/20">✉️</span>
                    Modèle de Lettre de Motivation (Anschreiben)
                </h1>
                <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-1">
                    Personnalisez votre modèle de base. Les marqueurs dynamiques sont automatiquement remplacés lors de chaque envoi.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 💡 Informational Banner -->
            <div class="bg-white dark:bg-stone-900 border border-amber-100 dark:border-stone-800 rounded-2xl p-6 shadow-sm space-y-3 relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-amber-500/5 rounded-full pointer-events-none"></div>
                <h3 class="font-bold text-stone-900 dark:text-white text-base flex items-center gap-2">
                    <span class="text-amber-700">💡</span> Marqueurs Dynamiques Automatiques
                </h3>
                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-400 leading-relaxed">
                    Lors de l'envoi d'un email, EasyApply injecte dynamiquement les informations spécifiques de l'entreprise dans votre texte :
                </p>

                <div class="space-y-2 pt-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 bg-stone-50 dark:bg-stone-800/60 rounded-xl border border-stone-200/80 dark:border-stone-700/80">
                        <span class="px-3 py-1 bg-white dark:bg-stone-900 border border-amber-200 dark:border-amber-800 rounded-lg text-xs font-mono font-bold text-amber-800 dark:text-amber-400 shrink-0">
                            Sehr geehrte(r) Frau/Herr [NOM_DIRECTEUR]
                        </span>
                        <span class="text-xs text-stone-600 dark:text-stone-400">
                            → Formule adaptée selon le genre : <strong class="text-stone-900 dark:text-white">Sehr geehrter Herr [NOM]</strong> (Homme), <strong class="text-stone-900 dark:text-white">Sehr geehrte Frau [NOM]</strong> (Femme), ou formule neutre.
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 bg-stone-50 dark:bg-stone-800/60 rounded-xl border border-stone-200/80 dark:border-stone-700/80">
                        <span class="px-3 py-1 bg-white dark:bg-stone-900 border border-amber-200 dark:border-amber-800 rounded-lg text-xs font-mono font-bold text-amber-800 dark:text-amber-400 shrink-0">
                            [TEXTE_PERSONNALISE]
                        </span>
                        <span class="text-xs text-stone-600 dark:text-stone-400">
                            → Paragraphe unique rédigé par l'IA Gemini (ou saisi manuellement) sur l'entreprise cible.
                        </span>
                    </div>
                </div>
            </div>

            <!-- 📝 Letter Editor Form -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-4">
                <form action="{{ route('lettre.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-2">
                            Contenu du Modèle de Lettre (Allemand / Anschreiben)
                        </label>
                        <textarea name="valeur" 
                                  rows="16" 
                                  required 
                                  class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 focus:bg-white dark:focus:bg-stone-800 text-stone-900 dark:text-white p-4 leading-relaxed transition duration-150">{{ old('valeur', $lettre) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-amber-800 via-amber-700 to-yellow-700 hover:from-amber-700 hover:to-yellow-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-amber-800/25 hover:shadow-amber-700/40 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Enregistrer le Modèle</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
