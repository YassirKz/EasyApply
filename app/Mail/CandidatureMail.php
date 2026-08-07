<?php

namespace App\Mail;

use App\Models\Entreprise;
use App\Models\Parametre;
use App\Models\CvSection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidatureMail extends Mailable
{
    use Queueable, SerializesModels;

    public Entreprise $entreprise;
    public string $lettreTexte;
    public string $pdfPath;

    /**
     * Build the automatic German gender-adapted salutation formula.
     */
    public static function buildSalutation(string $directeurRaw): string
    {
        $d = trim(strip_tags(htmlspecialchars_decode($directeurRaw, ENT_QUOTES)));

        if (empty($d) || in_array(strtolower($d), ['responsable recrutement', 'ausbildungsleitung', 'personalabteilung', 'hr', 'recruiter', 'team', 'non spécifié'])) {
            return "Sehr geehrte Damen und Herren";
        }

        // Female detection (Frau, Mrs, Ms, Madame, Mme)
        if (preg_match('/\b(frau|mrs|ms|madame|mme)\b/i', $d)) {
            $cleanName = trim(preg_replace('/\b(frau|mrs|ms|madame|mme)\b/i', '', $d));
            return "Sehr geehrte Frau " . ($cleanName ?: $d);
        }

        // Male detection (Herr, Mr, Monsieur, M.)
        if (preg_match('/\b(herr|mr|monsieur)\b/i', $d) || preg_match('/^m\.\s+/i', $d)) {
            $cleanName = trim(preg_replace('/\b(herr|mr|monsieur)\b/i', '', $d));
            $cleanName = trim(preg_replace('/^m\.\s+/i', '', $cleanName));
            return "Sehr geehrter Herr " . ($cleanName ?: $d);
        }

        // Fallback for neutral or un-prefixed names
        return "Sehr geehrte(r) Frau/Herr " . $d;
    }

    /**
     * Create a new message instance.
     */
    public function __construct(Entreprise $entreprise)
    {
        $this->entreprise = $entreprise;

        // Fetch letter template
        $templateParam = Parametre::where('cle', 'modele_lettre')->first();
        $template = $templateParam ? $templateParam->valeur : "Sehr geehrte(r) Frau/Herr [NOM_DIRECTEUR],\n\n[TEXTE_PERSONNALISE]\n\nMit freundlichen Grüßen,\nYassir Kezzi";

        // Replace placeholders securely with unescaped HTML characters for email
        $directeurClean = htmlspecialchars_decode(strip_tags($entreprise->directeur ?? ''), ENT_QUOTES);
        $texteClean = htmlspecialchars_decode(strip_tags($entreprise->texte_personnalise ?? ''), ENT_QUOTES);

        $salutation = self::buildSalutation($directeurClean);

        // Replace full salutation formula line if present
        $salutationPatterns = [
            'Sehr geehrte(r) Frau/Herr [NOM_DIRECTEUR]',
            'Sehr geehrte Frau/Herr [NOM_DIRECTEUR]',
            'Sehr geehrte(r) [NOM_DIRECTEUR]',
            '[FORMULE_SALUTATION]',
            '[SALUTATION]'
        ];

        $text = $template;
        foreach ($salutationPatterns as $pattern) {
            if (str_contains($text, $pattern)) {
                $text = str_replace($pattern, $salutation, $text);
            }
        }

        // Fallback replacement for any remaining [NOM_DIRECTEUR] tag
        $text = str_replace('[NOM_DIRECTEUR]', $directeurClean, $text);
        $text = str_replace('[TEXTE_PERSONNALISE]', $texteClean, $text);

        // Convert newlines to HTML breaks and encode HTML special characters securely
        $this->lettreTexte = nl2br(e($text));



        // Generate ATS CV PDF in memory / temp file
        $cvSections = CvSection::all()->keyBy('section');
        $pdf = Pdf::loadView('cv.pdf_template', compact('cvSections'));
        
        $tempDirectory = storage_path('app/temp');
        if (!file_exists($tempDirectory)) {
            mkdir($tempDirectory, 0755, true);
        }
        
        $this->pdfPath = $tempDirectory . '/CV_Yassir_Kezzi_' . $entreprise->id . '.pdf';
        $pdf->save($this->pdfPath);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bewerbung um eine Ausbildung als Fachinformatiker Anwendungsentwicklung - Yassir Kezzi',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.candidature',
            with: [
                'lettreTexte' => $this->lettreTexte,
                'entreprise' => $this->entreprise,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('CV_Yassir_Kezzi.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
