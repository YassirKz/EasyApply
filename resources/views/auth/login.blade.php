<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Heading -->
    <div class="mb-7 text-center">
        <h1 class="text-xl font-extrabold text-stone-900 dark:text-white tracking-tight">Bon retour 👋</h1>
        <p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Connectez-vous à votre compte EasyApply</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-[0.16em] text-stone-400 dark:text-stone-500 mb-1.5">
                Adresse e-mail
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="vous@exemple.com"
                class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-stone-700
                       bg-stone-50 dark:bg-stone-800
                       text-stone-900 dark:text-stone-100
                       placeholder-stone-400 dark:placeholder-stone-500
                       text-sm font-medium
                       focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-600 dark:focus:border-amber-500
                       transition duration-150"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold uppercase tracking-[0.16em] text-stone-400 dark:text-stone-500">
                    Mot de passe
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-amber-800 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-semibold transition duration-150">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-stone-700
                       bg-stone-50 dark:bg-stone-800
                       text-stone-900 dark:text-stone-100
                       placeholder-stone-400 dark:placeholder-stone-500
                       text-sm font-medium
                       focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-600 dark:focus:border-amber-500
                       transition duration-150"
            >
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div>
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer group">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-stone-300 dark:border-stone-600 text-amber-800 bg-stone-50 dark:bg-stone-800 shadow-sm focus:ring-amber-500 focus:ring-offset-0 transition duration-150"
                >
                <span class="text-sm text-stone-500 dark:text-stone-400 group-hover:text-stone-700 dark:group-hover:text-stone-300 transition duration-150">
                    Se souvenir de moi
                </span>
            </label>
        </div>

        <!-- Login Button — exact same gradient as platform primary buttons -->
        <button
            type="submit"
            id="btn-login"
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5
                   bg-gradient-to-r from-amber-800 to-yellow-700
                   hover:from-amber-700 hover:to-yellow-600
                   text-white font-bold text-sm rounded-xl
                   shadow-lg shadow-amber-800/20
                   hover:brightness-110
                   focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-stone-900
                   transition duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Se connecter
        </button>
    </form>

    <!-- Divider -->
    <div class="flex items-center gap-3 my-6">
        <div class="flex-1 h-px bg-stone-200 dark:bg-stone-700/60"></div>
        <span class="text-xs text-stone-400 dark:text-stone-500 font-medium px-1">Nouveau sur EasyApply ?</span>
        <div class="flex-1 h-px bg-stone-200 dark:bg-stone-700/60"></div>
    </div>

    <!-- Sign Up Button -->
    @if (Route::has('register'))
        <a href="{{ route('register') }}"
           id="btn-signup"
           class="w-full flex items-center justify-center gap-2 px-4 py-2.5
                  rounded-xl border border-stone-200 dark:border-stone-700
                  bg-stone-50 dark:bg-stone-800
                  text-stone-700 dark:text-stone-200
                  font-bold text-sm
                  hover:bg-stone-100 dark:hover:bg-stone-800/80
                  hover:border-amber-600/40 dark:hover:border-amber-600/40
                  hover:text-amber-800 dark:hover:text-amber-400
                  focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-stone-900
                  transition duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Créer un compte
        </a>
    @endif
</x-guest-layout>
