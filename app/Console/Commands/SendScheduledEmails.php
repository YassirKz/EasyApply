<?php

namespace App\Console\Commands;

use App\Jobs\SendCandidatureJob;
use App\Models\Entreprise;
use Illuminate\Console\Command;

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

        $dueEntreprises->each(fn (Entreprise $entreprise) => SendCandidatureJob::dispatch($entreprise->id));
        $this->info("Scheduled sending queued: {$dueEntreprises->count()} job(s).");
        return Command::SUCCESS;
    }
}
