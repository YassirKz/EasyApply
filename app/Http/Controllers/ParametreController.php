<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParametreController extends Controller
{
    public function index()
    {
        // Global Scope automatically filters by Auth::id()
        $parameter = Parametre::firstOrCreate(
            ['cle' => 'programme_envoyez'],
            ['valeur' => '08:00']
        );

        return view('parametres.index', [
            'programmeEnvoyez' => $parameter->valeur,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'programme_envoyez' => 'required|date_format:H:i',
        ], [
            'programme_envoyez.required' => 'Veuillez indiquer une heure d\'envoi.',
            'programme_envoyez.date_format' => 'Le format doit être HH:MM.',
        ]);

        // Global Scope + model booted() both ensure user_id is applied
        Parametre::updateOrCreate(
            ['cle' => 'programme_envoyez'],
            ['valeur' => $request->input('programme_envoyez')]
        );

        return redirect()->route('parametres.index')->with('success', 'Heure d\'envoi programmée mise à jour.');
    }
}
