<?php

namespace App\Console\Commands;

use App\Mail\RelanceMail;
use App\Models\Entreprise;
use App\Models\HistoriqueEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRelanceEmails extends Command
{
    protected $signature = 'relance:envoyer {--jours=14 : Nombre de jours après envoi pour relance}';

    protected $description = 'Envoyer des emails de relance aux entreprises sans réponse après X jours.';

    public function handle(): int
    {
        $jours = (int) $this->option('jours');
        $this->info("Relance après {$jours} jours.");

        $entreprises = Entreprise::where('est_envoye', true)
            ->where('statut_reponse', 'en_attente')
            ->where('date_envoi', '<=', now()->subDays($jours))
            ->get();

        if ($entreprises->isEmpty()) {
            $this->info('Aucune entreprise à relancer.');
            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($entreprises as $entreprise) {
            try {
                $relanceMail = new RelanceMail($entreprise);
                Mail::to($entreprise->email)->send($relanceMail);

                $entreprise->update([
                    'date_relance' => now(),
                    'nb_relances' => $entreprise->nb_relances + 1,
                    'statut_reponse' => 'relance_envoyee',
                ]);

                HistoriqueEmail::create([
                    'entreprise_id' => $entreprise->id,
                    'type' => 'relance',
                    'objet' => $relanceMail->envelope()->subject,
                    'contenu' => strip_tags($relanceMail->messageTexte),
                    'date_envoi' => now(),
                    'statut' => 'envoye',
                ]);

                $sent++;
                Log::info("Relance envoyée à {$entreprise->nom} ({$entreprise->email}).");

                if (app()->environment() !== 'testing') {
                    sleep(1);
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("Échec relance {$entreprise->nom}: {$e->getMessage()}");
            }
        }

        $this->info("Relances terminées : {$sent} envoyée(s), {$failed} échec(s).");
        return Command::SUCCESS;
    }
}
