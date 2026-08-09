<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-stone-900 dark:text-white tracking-tight">Paramètres d'envoi</h1>
                <p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Définissez l'heure à laquelle les envois programmés doivent démarrer chaque jour.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:!bg-stone-950 rounded-2xl shadow-sm border border-stone-200/80 dark:border-stone-800 overflow-hidden">
                <div class="p-6 bg-stone-50 dark:!bg-stone-950/50 border-b border-stone-200 dark:border-stone-800">
                    <h2 class="text-xl font-extrabold text-stone-900 dark:text-white">Paramètres d'envoi</h2>
                    <p class="mt-2 text-sm text-stone-500 dark:text-stone-400">Définissez l'heure à laquelle les envois programmés doivent démarrer chaque jour.</p>
                </div>

                <div class="p-6 bg-white dark:!bg-stone-950 space-y-6">
                    <form action="{{ route('parametres.store') }}" method="POST">
                        @csrf
                        <div class="grid gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-stone-700 dark:text-stone-200">Heure d'envoi automatique</label>
                                <input type="time" name="programme_envoyez" value="{{ old('programme_envoyez', $programmeEnvoyez) }}" class="mt-2 w-full rounded-2xl border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-stone-900 dark:text-white px-4 py-3 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" required>
                                @error('programme_envoyez')
                                    <p class="mt-2 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-stone-50 dark:bg-stone-950/50 rounded-3xl p-5 text-sm text-stone-700 dark:text-stone-300 border border-stone-200 dark:border-stone-800 shadow-sm">
                                <p class="font-semibold mb-3 text-stone-900 dark:text-white">Comment ça marche</p>
                                <ul class="space-y-2 list-disc list-inside text-sm text-stone-600 dark:text-stone-300">
                                    <li>L'application enverra automatiquement les candidatures programmées à l'heure choisie.</li>
                                    <li>Seules les entreprises avec <strong>est_envoye = false</strong> seront envoyées.</li>
                                    <li>Le scheduler doit être lancé avec <code class="bg-stone-900 text-stone-100 px-1 py-0.5 rounded">php artisan schedule:work</code> ou via cron.</li>
                                </ul>
                            </div>

                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-amber-800 to-amber-700 text-white rounded-2xl font-bold shadow-md shadow-amber-800/20 hover:from-amber-700 hover:to-amber-600 transition duration-200">
                                Enregistrer l'heure
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
