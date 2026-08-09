<?php

namespace App\Console\Commands;

use App\Mail\CandidatureMail;
use App\Models\Entreprise;
use App\Models\HistoriqueEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendScheduledEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled email applications whose scheduled timestamp is due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dueEntreprises = Entreprise::where('est_envoye', false)
            ->whereNotNull('programmation_envoi')
            ->where('programmation_envoi', '<=', now())
            ->get();

        if ($dueEntreprises->isEmpty()) {
            $this->info('No due scheduled emails to send.');
            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($dueEntreprises as $entreprise) {
            try {
                $candidatureMail = new CandidatureMail($entreprise);
                Mail::to($entreprise->email)->send($candidatureMail);

                $entreprise->update([
                    'est_envoye'          => true,
                    'date_envoi'          => now(),
                    'programmation_envoi' => null,
                ]);

                HistoriqueEmail::create([
                    'entreprise_id' => $entreprise->id,
                    'type' => 'candidature',
                    'objet' => $candidatureMail->envelope()->subject,
                    'contenu' => strip_tags($candidatureMail->lettreTexte),
                    'date_envoi' => now(),
                    'statut' => 'envoye',
                ]);

                $sent++;
                Log::info("Scheduled candidature sent to {$entreprise->nom} ({$entreprise->email})");
            } catch (\Exception $e) {
                $failed++;
                Log::error("Scheduled send failed for {$entreprise->nom} ({$entreprise->email}): " . $e->getMessage());
            }
        }

        $this->info("Scheduled sending finished: {$sent} sent, {$failed} failed.");
        return Command::SUCCESS;
    }
}
