<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    public function index(Request $request)
    {
        return Entreprise::query()->latest()->paginate(25);
    }

    public function show(Entreprise $entreprise)
    {
        $this->authorize('view', $entreprise);
        return $entreprise->load('historiqueEmails');
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $this->authorize('update', $entreprise);
        $data = $request->validate([
            'statut_reponse' => 'sometimes|in:en_attente,refuse,accepte,entretien_programme,en_cours,relance_envoyee',
            'notes' => 'nullable|string|max:1000',
            'date_reponse' => 'nullable|date',
        ]);
        $entreprise->update($data);
        return $entreprise->fresh();
    }
}
