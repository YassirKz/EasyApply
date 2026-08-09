<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueEmail;
use Illuminate\Http\Request;

class HistoriqueEmailController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['date_envoi', 'entreprise', 'type', 'objet', 'statut'];
        $sort = in_array($request->input('sort', 'date_envoi'), $allowedSorts, true)
            ? $request->input('sort', 'date_envoi')
            : 'date_envoi';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query = HistoriqueEmail::with('entreprise');

        if ($sort === 'entreprise') {
            $query->leftJoin('entreprises', 'historique_emails.entreprise_id', '=', 'entreprises.id')
                ->select('historique_emails.*')
                ->orderBy('entreprises.nom', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->filled('entreprise')) {
            $query->whereHas('entreprise', function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->input('entreprise') . '%');
            });
        }

        $historiqueEmails = $query->paginate(20)->withQueryString();

        return view('historique_emails.index', compact('historiqueEmails'));
    }
}
