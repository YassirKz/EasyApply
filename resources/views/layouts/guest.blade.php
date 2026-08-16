<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      :class="{ 'dark': darkMode }"
      x-init="$watch('darkMode', v => localStorage.setItem('darkMode', v))">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EasyApply') }} — Connexion</title>
        <meta name="description" content="EasyApply – Automatisez vos candidatures aux entreprises avec l'IA Gemini.">

        <!-- Inter Font from Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Vite Assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    {{-- Same background as the platform: bg-stone-50 dark:bg-stone-950 with amber blobs --}}
    <body class="font-sans antialiased bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 transition-colors duration-300 min-h-screen">

        {{-- Exact same animated background as app.blade.php --}}
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-400/10 dark:bg-amber-600/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-amber-800/10 dark:bg-amber-900/5 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-yellow-700/5 dark:bg-yellow-900/5 rounded-full blur-3xl"></div>
        </div>

        {{-- Mini top-bar matching the platform nav style --}}
        <div class="sticky top-0 z-40 bg-white/80 dark:bg-stone-900/80 backdrop-blur-md border-b border-stone-200/80 dark:border-stone-800/80 transition-colors duration-300">
            <div class="w-full max-w-full px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    {{-- Logo --}}
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-800 via-amber-700 to-yellow-700 flex items-center justify-center text-white font-extrabold text-sm shadow-md shadow-amber-800/25 group-hover:scale-105 group-hover:shadow-amber-700/40 transition duration-200">
                            EA
                        </div>
                        <span class="bg-gradient-to-r from-stone-900 via-amber-900 to-stone-800 dark:from-white dark:via-amber-200 dark:to-stone-200 bg-clip-text text-transparent font-extrabold tracking-tight text-lg">
                            Easy<span class="text-amber-800 dark:text-amber-400">Apply</span>
                        </span>
                    </a>

                    {{-- Dark Mode Toggle --}}
                    <button @click="darkMode = !darkMode"
                            type="button"
                            aria-label="Changer de thème"
                            class="p-2.5 rounded-xl text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 hover:text-stone-700 dark:hover:text-stone-200 transition duration-150">
                        <svg x-show="darkMode" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Centered content area --}}
        <div class="flex flex-col items-center justify-center min-h-[calc(100vh-64px)] px-4 py-12">

            {{-- Tagline above card --}}
            <p class="mb-6 text-xs text-stone-500 dark:text-stone-400 font-medium tracking-widest uppercase">
                Automatisez vos candidatures avec l'IA
            </p>

            {{-- Card — same style as platform cards: bg-white border-stone-200 rounded-3xl shadow-lg --}}
            <div class="w-full max-w-md bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl shadow-lg shadow-stone-200/30 dark:shadow-black/20 px-8 py-8 transition-colors duration-300">
                {{ $slot }}
            </div>

            {{-- Footer note --}}
            <p class="mt-8 text-xs text-stone-400 dark:text-stone-600 text-center">
                EasyApply &copy; {{ date('Y') }}   Yassir Kezzi &middot; Full-Stack Developer | Laravel | AI Gemini
            </p>
        </div>

    </body>
</html>
