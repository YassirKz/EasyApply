<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-800 to-amber-700 flex items-center justify-center text-white text-sm shadow-md shadow-amber-800/20">📄</span>
                    Modification du CV (Format ATS Allemand)
                </h1>
                <p class="text-xs sm:text-sm text-stone-500 dark:text-stone-400 mt-1">
                    Modifiez n'importe quelle section ci-dessous : votre CV PDF se régénère instantanément au format ATS 100% conforme.
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Preview Button -->
                <a href="{{ route('cv.pdf') }}" target="_blank" class="px-4 py-2.5 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-700 text-stone-700 dark:text-stone-200 font-bold text-xs sm:text-sm rounded-xl shadow-sm hover:shadow transition duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Aperçu PDF</span>
                </a>

                <!-- Download Button -->
                <a href="{{ route('cv.pdf', ['download' => 1]) }}" class="px-5 py-2.5 bg-gradient-to-r from-amber-800 via-amber-700 to-yellow-700 hover:from-amber-700 hover:to-yellow-600 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-amber-800/25 hover:shadow-amber-700/40 hover:scale-[1.02] active:scale-[0.98] transition duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Télécharger CV (ATS)</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 🛡️ ATS Compliance Banner -->
            <div class="bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-900/50 rounded-2xl p-4 text-xs sm:text-sm text-amber-900 dark:text-amber-300 leading-relaxed font-medium flex items-center gap-3">
                <span class="text-lg shrink-0">🛡️</span>
                <span><strong>Garantie ATS Compliance :</strong> Votre CV utilise une mise en page 1 colonne, une typographie standardisée et des entêtes explicites en allemand. Il franchit à 100% les filtres ATS des logiciels de recrutement allemands (Personio, Workday, SAP SuccessFactors, Taleo).</span>
            </div>

            <!-- 📁 Section: Documents Joints & Certificats (Anlagen) -->
            <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-stone-100 dark:border-stone-800 pb-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2">
                        <span>📁</span> Documents Joints & Certificats (Anlagen / Diplômes)
                    </h3>
                    @if($hasDocuments)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-xs font-bold rounded-full border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Document joint actif ({{ $documentsSizeFormatted }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-stone-100 dark:bg-stone-800 text-stone-500 dark:text-stone-400 text-xs font-bold rounded-full border border-stone-200 dark:border-stone-700">
                            Aucun document joint
                        </span>
                    @endif
                </div>

                <p class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed">
                    Ajoutez plusieurs PDF : EasyApply choisit le document du même secteur que l'entreprise, puis le document marqué par défaut.
                </p>

                @if($documents->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($documents as $document)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-stone-200 dark:border-stone-700 p-3 text-xs">
                                <div><strong>{{ $document->nom }}</strong> — {{ $document->secteur ?: 'Tous secteurs' }} @if($document->est_defaut)<span class="text-emerald-600">· Par défaut</span>@endif</div>
                                <div class="flex gap-2"><a class="text-amber-700 font-bold" href="{{ route('cv.documents.show', $document) }}">Télécharger</a><form method="POST" action="{{ route('cv.documents.destroy', $document) }}">@csrf @method('DELETE')<button class="text-rose-600 font-bold">Supprimer</button></form></div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($hasDocuments)
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-900/50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📑</span>
                            <div>
                                <p class="text-xs font-bold text-stone-900 dark:text-white">Anlagen_Yassir_Kezzi.pdf</p>
                                <p class="text-[11px] text-stone-500 dark:text-stone-400 mt-0.5">Taille: {{ $documentsSizeFormatted }} · Fichier prêt pour l'envoi</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('cv.documents.download') }}" class="px-3.5 py-2 bg-stone-900 dark:bg-stone-700 hover:bg-stone-800 text-white font-bold text-xs rounded-xl shadow-sm transition duration-150 flex items-center gap-1.5">
                                <span>📥 Télécharger</span>
                            </a>
                            <form action="{{ route('cv.documents.delete') }}" method="POST" onsubmit="return confirm('Supprimer le fichier de documents joint ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 text-rose-600 dark:text-rose-400 font-bold text-xs rounded-xl border border-rose-200 dark:border-rose-900/60 transition">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <form action="{{ route('cv.documents.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-2">
                    @csrf
                    <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider">Ajouter un PDF (max. 15 Mo)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input name="nom" placeholder="Nom du document (ex. CV IT)" class="rounded-xl border-stone-200 dark:border-stone-700 dark:bg-stone-800 text-sm">
                        <input name="secteur" placeholder="Secteur ciblé (optionnel)" class="rounded-xl border-stone-200 dark:border-stone-700 dark:bg-stone-800 text-sm">
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs"><input type="checkbox" name="est_defaut" value="1"> Utiliser par défaut</label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <input type="file" 
                               name="document" 
                               accept="application/pdf" 
                               required
                               class="flex-1 text-xs text-stone-500 dark:text-stone-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 dark:file:bg-stone-800 dark:file:text-amber-400 border border-stone-200 dark:border-stone-700 rounded-xl p-1.5 cursor-pointer">
                        <button type="submit" class="px-5 py-2.5 bg-amber-800 hover:bg-amber-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition duration-150 shrink-0 flex items-center justify-center gap-1.5">
                            <span>📤 Téléverser PDF</span>
                        </button>
                    </div>
                </form>
            </div>

            <form action="{{ route('cv.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @php
                    $userPhoto = \App\Http\Controllers\LettreCvController::getPhotoPath();
                    $hasExistingPhoto = file_exists($userPhoto);
                    $existingPhotoUrl = $hasExistingPhoto ? route('cv.photo') . '?v=' . filemtime($userPhoto) : null;
                @endphp

                <!-- 📷 Section: Photo de Profil (Bewerbungsfoto) -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-4" x-data="{ previewUrl: '{{ $existingPhotoUrl }}' }">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        <span>📷</span> Photo de Profil (Bewerbungsfoto)
                    </h3>
                    <div class="flex items-center gap-6">
                        <template x-if="previewUrl">
                            <div class="shrink-0 relative">
                                <img :src="previewUrl" alt="Bewerbungsfoto" class="w-24 h-32 object-cover rounded-xl border-2 border-amber-700/30 shadow-md">
                                <span class="absolute -bottom-2 -right-2 bg-emerald-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold shadow">Active</span>
                            </div>
                        </template>
                        <template x-if="!previewUrl">
                            <div class="w-24 h-32 bg-stone-100 dark:bg-stone-800 rounded-xl border-2 border-dashed border-stone-300 dark:border-stone-700 flex flex-col items-center justify-center text-stone-400 text-xs text-center p-2">
                                <span class="text-2xl mb-1">🖼️</span>
                                <span>Aucune photo</span>
                            </div>
                        </template>

                        <div class="space-y-2 flex-grow">
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider">Télécharger / Modifier votre Photo (JPG, PNG, WEBP)</label>
                            <input type="file" 
                                   name="photo" 
                                   accept="image/*" 
                                   @change="
                                       const file = $event.target.files[0];
                                       if (file) {
                                           const reader = new FileReader();
                                           reader.onload = (e) => { previewUrl = e.target.result; };
                                           reader.readAsDataURL(file);
                                       }
                                   "
                                   class="w-full text-xs text-stone-500 dark:text-stone-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 dark:file:bg-stone-800 dark:file:text-amber-400 border border-stone-200 dark:border-stone-700 rounded-xl p-1.5 cursor-pointer">
                            <p class="text-xs text-stone-400">Cliquez sur <strong>Enregistrer les modifications</strong> en bas pour valider.</p>
                        </div>
                    </div>
                </div>

                <!-- 🏷️ Section: En-tête du CV ATS (Nom, Titre, Contact, Liens) -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-4">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        <span>🏷️</span> En-tête du CV (Informations d'En-tête ATS)
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">
                                Nom & Prénom
                            </label>
                            <input type="text" disabled value="{{ Auth::user()->name }} ({{ Auth::user()->email }})" class="w-full text-xs font-semibold bg-stone-100 dark:bg-stone-800/60 border-stone-200 dark:border-stone-700 rounded-xl text-stone-500 dark:text-stone-400 p-2.5">
                            <p class="text-[11px] text-stone-400 mt-1">Affiché automatiquement dans l'en-tête du CV.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">
                                Titre du CV / Poste visé
                            </label>
                            <input type="text" name="cv_subtitle" value="{{ old('cv_subtitle', $parametres['cv_subtitle']->valeur ?? 'Junior Full-Stack Entwickler · Ausbildung Fachinformatiker') }}" placeholder="Ex: Junior Full-Stack Entwickler · Ausbildung Fachinformatiker" class="w-full text-xs sm:text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white p-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">
                                Numéro de Téléphone
                            </label>
                            <input type="text" name="cv_phone" value="{{ old('cv_phone', $parametres['cv_phone']->valeur ?? '+212 682 486 661') }}" placeholder="Ex: +212 682 486 661" class="w-full text-xs sm:text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white p-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider mb-1">
                                Liens Réseaux / Portfolio (LinkedIn, GitHub...)
                            </label>
                            <input type="text" name="cv_links" value="{{ old('cv_links', $parametres['cv_links']->valeur ?? 'linkedin.com/in/yassir-kezzi-530383283/ | github.com/YassirKz') }}" placeholder="Ex: linkedin.com/in/monprofil | github.com/moncompte" class="w-full text-xs sm:text-sm bg-stone-50 dark:bg-stone-800 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white p-2.5">
                        </div>
                    </div>
                </div>

                <!-- 👤 Section 1: Profil -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        👤 1. BERUFLICHES PROFIL (Profil Professionnel)
                    </h3>
                    <textarea name="profil" rows="5" class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white leading-relaxed">{{ old('profil', $sections['profil']->contenu ?? '') }}</textarea>
                </div>

                <!-- 💻 Section 2: Compétences -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        💻 2. TECHNISCHE KOMPETENZEN (Compétences Techniques)
                    </h3>
                    <textarea name="competences" rows="5" class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white leading-relaxed">{{ old('competences', $sections['competences']->contenu ?? '') }}</textarea>
                </div>

                <!-- 🏢 Section 3: Praktikum -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        🏢 3. PRAKTIKUM (Stage & Expérience Pratique)
                    </h3>
                    <textarea name="praktikum" rows="5" class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white leading-relaxed">{{ old('praktikum', $sections['praktikum']->contenu ?? '') }}</textarea>
                </div>

                <!-- 🚀 Section 4: Projekterfahrung -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        🚀 4. PROJEKTERFAHRUNG (Projets & Réalisations)
                    </h3>
                    <textarea name="projekterfahrung" rows="10" class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white leading-relaxed">{{ old('projekterfahrung', $sections['projekterfahrung']->contenu ?? '') }}</textarea>
                </div>

                <!-- 🎓 Section 5: Ausbildung -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        🎓 5. AUSBILDUNG (Parcours Scolaire & Formations)
                    </h3>
                    <textarea name="ausbildung" rows="6" class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white leading-relaxed">{{ old('ausbildung', $sections['ausbildung']->contenu ?? '') }}</textarea>
                </div>

                <!-- 🌐 Section 6: Langues -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-slate-800 p-6 space-y-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        🌐 6. SPRACHKENNTNISSE (Langues)
                    </h3>
                    <textarea name="langues" rows="4" class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white leading-relaxed">{{ old('langues', $sections['langues']->contenu ?? '') }}</textarea>
                </div>

                <!-- 📋 Section 7: Persönliche Daten -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        📋 7. PERSÖNLICHE DATEN (Informations Personnelles)
                    </h3>
                    <textarea name="personliche_daten" rows="5" class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white leading-relaxed">{{ old('personliche_daten', $sections['personliche_daten']->contenu ?? '') }}</textarea>
                </div>

                <!-- ⭐ Section 8: Interessen -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 p-6 space-y-3">
                    <h3 class="font-extrabold text-base text-stone-900 dark:text-white flex items-center gap-2 border-b border-stone-100 dark:border-stone-800 pb-3">
                        ⭐ 8. INTERESSEN (Centres d'Intérêt)
                    </h3>
                    <textarea name="interessen" rows="3" class="w-full text-xs sm:text-sm font-mono bg-stone-50 dark:bg-stone-800/80 border-stone-200 dark:border-stone-700 rounded-xl focus:ring-2 focus:ring-amber-500 text-stone-900 dark:text-white leading-relaxed">{{ old('interessen', $sections['interessen']->contenu ?? '') }}</textarea>
                </div>

                <!-- Submit CTA -->
                <div class="flex justify-end pt-2 pb-6">
                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-amber-800 via-amber-700 to-yellow-700 hover:from-amber-700 hover:to-yellow-600 text-white font-bold text-sm sm:text-base rounded-xl shadow-lg shadow-amber-800/25 hover:shadow-amber-700/40 hover:scale-[1.01] active:scale-[0.99] transition duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span>Enregistrer les Modifications du CV</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
