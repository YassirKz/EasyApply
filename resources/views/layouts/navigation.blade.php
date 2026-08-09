<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/80 dark:bg-stone-900/80 backdrop-blur-md border-b border-stone-200/80 dark:border-stone-800/80 transition-colors duration-300">
    <!-- Primary Navigation Menu -->
    <div class="w-full max-w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center font-black text-xl tracking-tight">
                    <a href="{{ route('entreprises.index') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-800 via-amber-700 to-yellow-700 flex items-center justify-center text-white font-extrabold text-sm shadow-md shadow-amber-800/25 group-hover:scale-105 group-hover:shadow-amber-700/40 transition duration-200">
                            EA
                        </div>
                        <span class="bg-gradient-to-r from-stone-900 via-amber-900 to-stone-800 dark:from-white dark:via-amber-200 dark:to-stone-200 bg-clip-text text-transparent font-extrabold tracking-tight text-lg">
                            Easy<span class="text-amber-800 dark:text-amber-400">Apply</span>
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:flex items-center">
                    <a href="{{ route('dashboard') }}"
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition duration-150 flex items-center gap-2 {{ request()->routeIs('dashboard') ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100/70 dark:hover:bg-stone-800/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('entreprises.index') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition duration-150 flex items-center gap-2 {{ request()->routeIs('entreprises.*') ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100/70 dark:hover:bg-stone-800/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Entreprises</span>
                    </a>

                    <a href="{{ route('lettre.edit') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition duration-150 flex items-center gap-2 {{ request()->routeIs('lettre.*') ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100/70 dark:hover:bg-stone-800/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Lettre de motivation</span>
                    </a>

                    <a href="{{ route('cv.edit') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition duration-150 flex items-center gap-2 {{ request()->routeIs('cv.edit') ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100/70 dark:hover:bg-stone-800/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Mon CV</span>
                    </a>

                    <a href="{{ route('parametres.index') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition duration-150 flex items-center gap-2 {{ request()->routeIs('parametres.*') ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100/70 dark:hover:bg-stone-800/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 16v-2m8-8h2M4 12H2m16.364 5.364l1.414 1.414M6.222 6.222L4.808 4.808m0 14.142l1.414-1.414M17.778 6.222l1.414-1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                        <span>Paramètres</span>
                    </a>

                    <a href="{{ route('historique.index') }}" 
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold transition duration-150 flex items-center gap-2 {{ request()->routeIs('historique.*') ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400' : 'text-stone-600 dark:text-stone-400 hover:text-stone-900 dark:hover:text-white hover:bg-stone-100/70 dark:hover:bg-stone-800/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Historique</span>
                    </a>

                    <a href="{{ route('cv.pdf') }}" target="_blank" 
                       class="px-3.5 py-2 rounded-xl text-sm font-semibold text-stone-600 dark:text-stone-400 hover:text-amber-800 dark:hover:text-amber-400 hover:bg-stone-100/70 dark:hover:bg-stone-800/50 transition duration-150 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Aperçu PDF (ATS)</span>
                    </a>
                </div>
            </div>

            <!-- Right Utilities & Profile Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <!-- Dark Mode Toggle Button -->
                <button @click="darkMode = !darkMode" 
                        type="button" 
                        aria-label="Changer de thème"
                        class="p-2.5 rounded-xl text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 hover:text-stone-700 dark:hover:text-stone-200 transition duration-150">
                    <!-- Sun Icon (shows in dark mode) -->
                    <svg x-show="darkMode" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <!-- Moon Icon (shows in light mode) -->
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                <!-- Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-sm font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 transition duration-150 focus:outline-none">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber-800 to-amber-700 dark:from-amber-700 dark:to-amber-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-stone-100 dark:border-stone-800">
                            <p class="text-xs text-stone-400 font-medium">Connecté en tant que</p>
                            <p class="text-xs font-bold text-stone-800 dark:text-stone-200 truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2 text-xs">
                            <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Mon Profil</span>
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center gap-2 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Déconnexion</span>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden gap-2">
                <button @click="darkMode = !darkMode" type="button" class="p-2 text-stone-500 rounded-lg">
                    <svg x-show="darkMode" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <button @click="open = ! open" class="p-2 rounded-xl text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800 transition duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white/95 dark:bg-stone-900/95 border-b border-stone-200 dark:border-stone-800 px-4 pt-2 pb-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-base font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800">📊 Dashboard</a>
        <a href="{{ route('entreprises.index') }}" class="block px-3 py-2 rounded-xl text-base font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800">🏢 Entreprises</a>
        <a href="{{ route('lettre.edit') }}" class="block px-3 py-2 rounded-xl text-base font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800">✉️ Modifier ma lettre</a>
        <a href="{{ route('cv.edit') }}" class="block px-3 py-2 rounded-xl text-base font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800">📄 Modifier mon CV</a>
        <a href="{{ route('parametres.index') }}" class="block px-3 py-2 rounded-xl text-base font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800">⚙️ Paramètres</a>
        <a href="{{ route('historique.index') }}" class="block px-3 py-2 rounded-xl text-base font-semibold text-stone-700 dark:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800">📜 Historique</a>
        <a href="{{ route('cv.pdf') }}" target="_blank" class="block px-3 py-2 rounded-xl text-base font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40">📑 Aperçu CV PDF (ATS)</a>
    </div>
</nav>
