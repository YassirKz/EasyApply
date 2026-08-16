<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Parametre;
use App\Models\CvSection;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Initialize default motivation letter template for the new user
        Parametre::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'cle'     => 'modele_lettre',
            'valeur'  => "Sehr geehrte(r) Frau/Herr [NOM_DIRECTEUR],\n\n[TEXTE_PERSONNALISE]\n\nMit freundlichen Grüßen,\n" . $user->name,
        ]);

        Parametre::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'cle'     => 'programme_envoyez',
            'valeur'  => '08:00',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))->with('success', 'Bienvenue sur EasyApply ! Votre compte a été créé avec succès.');
    }
}
