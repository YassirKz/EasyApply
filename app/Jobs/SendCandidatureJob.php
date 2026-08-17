<?php

namespace App\Jobs;

use App\Mail\CandidatureMail;
use App\Models\Entreprise;
use App\Models\HistoriqueEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendCandidatureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public readonly int $entrepriseId) {}

    public function handle(): void
    {
        DB::transaction(function (): void {
            $entreprise = Entreprise::withoutGlobalScopes()->lockForUpdate()->findOrFail($this->entrepriseId);

            if ($entreprise->est_envoye) {
                return;
            }

            $mail = new CandidatureMail($entreprise);
            Mail::to($entreprise->email)->send($mail);

            $entreprise->update(['est_envoye' => true, 'date_envoi' => now(), 'programmation_envoi' => null]);
            HistoriqueEmail::create([
                'entreprise_id' => $entreprise->id,
                'type' => 'candidature',
                'objet' => $mail->envelope()->subject,
                'contenu' => strip_tags($mail->lettreTexte),
                'date_envoi' => now(),
                'statut' => 'envoye',
            ]);
        });
    }
}
