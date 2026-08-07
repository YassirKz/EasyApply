<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" x-init="$watch('darkMode', v => localStorage.setItem('darkMode', v))">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EasyApply') }} — Candidatures Automatisées</title>
        <meta name="description" content="EasyApply – Automatisez vos candidatures aux entreprises allemandes avec l'IA Gemini.">

        <!-- Inter Font from Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Vite Assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 transition-colors duration-300">

        <!-- Animated background gradient — warm brown tones -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-400/10 dark:bg-amber-600/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-amber-800/10 dark:bg-amber-900/5 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-yellow-700/5 dark:bg-yellow-900/5 rounded-full blur-3xl"></div>
        </div>

        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/80 dark:bg-stone-900/80 backdrop-blur-md border-b border-stone-200/60 dark:border-stone-700/60 shadow-sm sticky top-[64px] z-30">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="mt-auto border-t border-stone-200/60 dark:border-stone-700/60 bg-white/50 dark:bg-stone-900/50 backdrop-blur-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs text-stone-400 dark:text-stone-500">
                        <span class="bg-gradient-to-r from-amber-800 to-amber-900 text-white text-xs font-black px-2 py-0.5 rounded-md">EA</span>
                        <span>EasyApply &copy; {{ date('Y') }} — Yassir Kezzi</span>
                    </div>
                    <div class="text-xs text-stone-400 dark:text-stone-500 hidden sm:block">
                        Full-Stack Developer · Laravel · AI Gemini
                    </div>
                </div>
            </footer>
        </div>

        <!-- Global Toast Notification System -->
        <div id="toast-container" class="fixed bottom-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none">
        </div>

        <!-- Toast JS -->
        <script>
            function showToast(message, type = 'success') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                const icons = {
                    success: `<svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
                    error:   `<svg class="w-5 h-5 text-rose-400"    fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
                    info:    `<svg class="w-5 h-5 text-amber-500"   fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
                };
                toast.className = 'pointer-events-auto flex items-start gap-3 px-4 py-3 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 rounded-2xl shadow-xl shadow-stone-200/50 dark:shadow-stone-900/50 max-w-sm text-sm font-medium text-stone-700 dark:text-stone-200 translate-y-2 opacity-0 transition-all duration-300';
                toast.innerHTML = `${icons[type] || icons.success}<span class="flex-1">${message}</span><button onclick="this.parentElement.remove()" class="text-stone-300 hover:text-stone-500 dark:hover:text-stone-300 font-bold text-lg leading-none">×</button>`;
                container.appendChild(toast);
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        toast.classList.remove('translate-y-2', 'opacity-0');
                    });
                });
                setTimeout(() => {
                    toast.classList.add('translate-y-2', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 4500);
            }

            // Auto-show server-side flash messages as toasts
            document.addEventListener('DOMContentLoaded', () => {
                @if(session('success'))
                    showToast(@json(session('success')), 'success');
                @endif
                @if(session('error'))
                    showToast(@json(session('error')), 'error');
                @endif
                @if(session('info'))
                    showToast(@json(session('info')), 'info');
                @endif
            });
        </script>
    </body>
</html>
