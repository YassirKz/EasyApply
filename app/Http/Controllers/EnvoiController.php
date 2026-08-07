<?php

namespace App\Http\Controllers;

use App\Mail\CandidatureMail;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EnvoiController extends Controller
{
    /**
     * Send email applications to all pending entreprises (est_envoye == 0).
     */
    public function envoyerMasse(Request $request)
    {
        // Prevent PHP execution timeout during bulk email dispatch
        set_time_limit(0);

        // Select only companies pending immediate send (exclude future scheduled ones)
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

        $envoisReussis = 0;
        $envoisEchoues = 0;

        foreach ($entreprises as $entreprise) {
            try {
                // Send email with attached ATS CV PDF
                Mail::to($entreprise->email)->send(new CandidatureMail($entreprise));

                // Mark as sent
                $entreprise->update([
                    'est_envoye' => true,
                    'date_envoi' => now(),
                ]);

                $envoisReussis++;
                Log::info("Candidature envoyée avec succès à : {$entreprise->nom} ({$entreprise->email})");

                // Pause 1 second between emails to prevent SMTP server rate limiting (skip in tests)
                if (app()->environment() !== 'testing') {
                    sleep(1);
                }
            } catch (\Exception $e) {
                $envoisEchoues++;
                Log::error("Échec d'envoi à {$entreprise->nom} ({$entreprise->email}): " . $e->getMessage());
            }
        }

        $msg = "Opération terminée : {$envoisReussis} email(s) envoyé(s) avec succès.";
        if ($envoisEchoues > 0) {
            $msg .= " {$envoisEchoues} échec(s) enregistré(s) dans les logs.";
        }

        return redirect()->route('entreprises.index')->with('success', $msg);
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

        $testEmail = env('MAIL_FROM_ADDRESS', 'kezziyassir005@gmail.com');

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
        $entreprise->update(['programmation_envoi' => null]);

        return redirect()->route('entreprises.index')
                         ->with('success', "Programmation annulée pour {$entreprise->nom}.");
    }
}
