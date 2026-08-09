<?php

namespace App\Mail;

use App\Models\Entreprise;
use App\Models\Parametre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RelanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Entreprise $entreprise;
    public string $messageTexte;

    public function __construct(Entreprise $entreprise)
    {
        $this->entreprise = $entreprise;

        $templateParam = Parametre::where('cle', 'modele_relance')->first();
        $template = $templateParam ? $templateParam->valeur : "Hallo [NOM_DIRECTEUR],\n\nIch wollte freundlich nach dem Stand meiner Bewerbung fragen.\n\nMit freundlichen Grüßen,\nYassir Kezzi";

        $directeurClean = htmlspecialchars_decode(strip_tags($entreprise->directeur ?? ''), ENT_QUOTES);
        $text = str_replace('[NOM_DIRECTEUR]', $directeurClean, $template);

        $this->messageTexte = nl2br(e($text));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relance : Suivi de candidature - Yassir Kezzi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.relance',
            with: [
                'messageTexte' => $this->messageTexte,
                'entreprise' => $this->entreprise,
            ],
        );
    }
}
