<?php

namespace App\Http\Controllers;

use App\Mail\CandidatureMail;
use App\Models\Entreprise;
use App\Jobs\SendCandidatureJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnvoiController extends Controller
{
    /**
     * Preview mass send before confirming.
     */
    public function preview(Request $request)
    {
        $entreprises = Entreprise::where('est_envoye', false)
            ->where(function ($q) {
                $q->whereNull('programmation_envoi')
                  ->orWhere('programmation_envoi', '<=', now());
            })
            ->get();

        if ($entreprises->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune entreprise prête à l\'envoi immédiat.',
            ], 422);
        }

        $firstEntreprise = $entreprises->first();
        $candidatureMail = new CandidatureMail($firstEntreprise);

        return response()->json([
            'success' => true,
            'count' => $entreprises->count(),
            'preview_html' => $candidatureMail->lettreTexte,
            'companies' => $entreprises->map(function ($entreprise) {
                return [
                    'nom' => $entreprise->nom,
                    'email' => $entreprise->email,
                ];
            })->toArray(),
        ]);
    }

    /**
     * Send email applications to all pending entreprises (est_envoye == 0).
     */
    public function envoyerMasse(Request $request)
    {
        // The request must never wait on SMTP or PDF generation. Each company is
        // claimed and sent by a queued job.
        $entreprises = Entreprise::where('est_envoye', false)
            ->where(function ($q) {
                $q->whereNull('programmation_envoi')
                  ->orWhere('programmation_envoi', '<=', now());
            })
            ->get();

        if ($entreprises->isEmpty()) {
            return redirect()->route('entreprises.index')
                             ->with('info', 'Aucune candidature prête pour envoi immédiat. Les candidatures programmées seront envoyées à l\'heure prévue.');
        }

        $entreprises->each(fn (Entreprise $entreprise) => SendCandidatureJob::dispatch($entreprise->id));

        return redirect()->route('entreprises.index')
            ->with('success', "{$entreprises->count()} candidature(s) ajoutée(s) à la file d’envoi.");
    }

    /**
     * Send a test email to yourself to preview how it looks.
     */
    public function envoyerTest(Request $request)
    {
        // Use first pending company as preview, fallback to any company
        $entreprise = Entreprise::where('est_envoye', false)->first()
                   ?? Entreprise::first();

        if (!$entreprise) {
            return redirect()->route('entreprises.index')
                             ->with('error', 'Aucune entreprise disponible pour le test. Ajoutez d\'abord une entreprise.');
        }

        $testEmail = config('mail.from.address');

        try {
            Mail::to($testEmail)->send(new CandidatureMail($entreprise));
            return redirect()->route('entreprises.index')
                             ->with('success', "✅ Email test envoyé à {$testEmail} ! Vérifiez votre boîte Gmail. (Entreprise de test : {$entreprise->nom})");
        } catch (\Exception $e) {
            Log::error('Test email failed: ' . $e->getMessage());
            return redirect()->route('entreprises.index')
                             ->with('error', '❌ Erreur lors de l\'envoi test : ' . $e->getMessage());
        }
    }

    /**
     * Schedule mass email dispatch for pending companies at a specific datetime.
     */
    public function programmerMasse(Request $request)
    {
        $request->validate([
            'programmation_envoi' => 'required|date|after:now',
        ], [
            'programmation_envoi.required' => 'Veuillez choisir une date et heure d\'envoi.',
            'programmation_envoi.after'    => 'La date de programmation doit être dans le futur.',
        ]);

        $entreprises = Entreprise::where('est_envoye', false)->get();

        if ($entreprises->isEmpty()) {
            return redirect()->route('entreprises.index')
                             ->with('info', 'Aucune candidature en attente d\'envoi.');
        }

        $scheduledAt = \Illuminate\Support\Carbon::parse($request->input('programmation_envoi'));

        foreach ($entreprises as $e) {
            $e->update(['programmation_envoi' => $scheduledAt]);
        }

        return redirect()->route('entreprises.index')
                         ->with('success', "🗓️ Envoi de {$entreprises->count()} candidature(s) programmé pour le " . $scheduledAt->format('d/m/Y à H:i') . " !");
    }

    /**
     * Cancel scheduled dispatch for a specific entreprise.
     */
    public function annulerProgrammation(Entreprise $entreprise)
    {
        $this->authorize('update', $entreprise);
        $entreprise->update(['programmation_envoi' => null]);

        return redirect()->route('entreprises.index')
                         ->with('success', "Programmation annulée pour {$entreprise->nom}.");
    }
}
