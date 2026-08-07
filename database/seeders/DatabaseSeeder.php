<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Parametre;
use App\Models\CvSection;
use App\Models\Entreprise;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        User::firstOrCreate(
            ['email' => 'yassir@easyapply.com'],
            [
                'name' => 'Yassir Kezzi',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Create Default Letter Template in Parametres
        Parametre::firstOrCreate(
            ['cle' => 'modele_lettre'],
            [
                'valeur' => "Sehr geehrte(r) Frau/Herr [NOM_DIRECTEUR],\n\nmit großem Interesse bewerbe ich mich um eine Ausbildung als Fachinformatiker für Anwendungsentwicklung in Ihrem Unternehmen.\n\n[TEXTE_PERSONNALISE]\n\nAls engagierter Full-Stack Entwickler bringe ich fundierte Kenntnisse in Laravel, PHP, JavaScript und modernen Webtechnologien mit. Ich freue mich über die Gelegenheit, mein Können und meine Begeisterung für die Softwareentwicklung in Ihr Team einzubringen.\n\nMit freundlichen Grüßen,\nYassir Kezzi"
            ]
        );

        // 3. Create Default CV Sections (Yassir Kezzi Exact German CV)
        $sections = [
            [
                'section' => 'profil',
                'contenu' => "Ich bin Yassir, 20 Jahre alt, leidenschaftlicher Full-Stack-Entwickler und jemand, der aus Ideen funktionierende Produkte macht. Meine Reise begann mit purem Staunen darüber, wie ein paar Zeilen Code etwas Sichtbares, Nutzbares entstehen lassen können. Heute entwickle ich eigenständig komplette Webanwendungen: Von der Datenbankarchitektur bis zur fertigen Benutzeroberfläche.\n\nMein bisher größtes Projekt, Smiris Learn, ist eine mehrsprachige SaaS-Lernplattform für Unternehmen, die ich von der Konzeption bis zur Zahlung Integration komplett allein umgesetzt habe. Dieses Projekt hat mir gezeigt, dass ich auch in komplexen Situationen strukturiert denke, Probleme selbstständig löse und nicht aufgebe, bevor etwas wirklich funktioniert.\n\nIch suche eine Ausbildungsstelle in Deutschland, bei der ich mein Wissen einbringen, täglich wachsen und Teil eines Teams sein kann, das gute Software baut. Meine Deutschkenntnisse (B1, aktiv in Weiterentwicklung) und meine Bereitschaft zum Umzug machen einen baldigen Start möglich."
            ],
            [
                'section' => 'competences',
                'contenu' => "Frontend: HTML5, CSS3, JavaScript (ES6+), React, Vite, Tailwind CSS, Bootstrap, Framer Motion, Responsive Design\nBackend & DB: PHP, MySQL, PostgreSQL, Supabase (Auth, Storage, Edge Functions)\nZahlungssysteme: Stripe – Checkout, Webhooks\nTools & Methoden: Git, GitHub, Scrum / Agile, phpMyAdmin, Jira\nIn Weiterbildung: Laravel, Node.js, MongoDB, TypeScript, StarUML, Cloud"
            ],
            [
                'section' => 'praktikum',
                'contenu' => "Smiris Academy (Dortmund – 100% Remote)\nPraktikant Full-Stack-Entwicklung (1 Monat)\n\nIn nur einem Monat habe ich Smiris Learn entwickelt – eine eigene Lernplattform, die die bisherige, teure externe Lösung ersetzt. Das war ein echtes „Learning by Doing“-Erlebnis: Ich habe das Frontend mit React und Tailwind gebaut, die Datenbank mit Supabase eingerichtet, Stripe-Zahlungen integriert und jeden Tag per Video-Call mit dem CEO abgestimmt. Am Ende stand eine Plattform, die intern eingesetzt wird, über 85% Abschlussquote erreicht und dem Unternehmen 70% Kosten spart."
            ],
            [
                'section' => 'projekterfahrung',
                'contenu' => "Smiris Learn – SaaS-Lernplattform für Unternehmensschulungen (in Entwicklung)\nReact, Vite, Tailwind CSS, Framer Motion, Supabase, Stripe\nhttps://smiris-learn.vercel.app/\n\n• Für dieses Projekt habe ich von Grund auf eine mandantenfähige B2B-Lernplattform realisiert – von der ersten Idee bis zur lauffähigen Anwendung. Die Plattform richtet sich an Unternehmen, die ihre Mitarbeiter strukturiert weiterbilden möchten, und unterstützt drei unterschiedliche Benutzerrollen: Super Admin, Firmen-Admin und Lernende.\n• Frontend mit moderner Optik: Ein durchgängig responsives Interface im Glas Morphism-Stil mit integriertem Dark Mode. Die Oberfläche wurde mit React und Framer Motion umgesetzt.\n• Sicheres und skalierbares Backend: Als Basis dient Supabase (PostgreSQL, Echtzeit, Auth, Storage) mit Row Level Security (RLS).\n• Zahlungsabwicklung mit Stripe: Checkout für Abonnements sowie Customer Portal zur Verwaltung bestehender Verträge, verarbeitet via Supabase Edge Function Webhooks.\n• Flexibler Quiz-Builder: Quiz-Erstellung mit Zeitlimit, Wiederholungslimit und Mindestpunktzahl.\n• Linearer Video-Player: Lernfortschritt-Speicherung und Videos aus Supabase Storage über signierte URLs.\n\nQuiz Academy\nPHP, MySQL\n• Dynamische Quiz-Anwendung mit Datenbankanbindung – gezieltes Üben des Frontend-Backend-Zusammenspiels.\n\nUSD-MAD Converter (in Entwicklung)\nJavaScript\n• Echtzeit-Währungsrechner (USD ⇆ MAD)\n\nPersönliches Portfolio (in Entwicklung)\nReact.js\n• Portfolio-Website zur Präsentation eigener Projekte und Fähigkeiten."
            ],
            [
                'section' => 'ausbildung',
                'contenu' => "Full-Stack-Webentwickler (in Ausbildung)\nOFPPT NTIC, Rabat | 2024 – heute\n• Frontend- und Backend-Entwicklung, relationale Datenbanken, agile Methoden (Scrum).\n\nPräsenzausbildung Webentwicklung & Programmierung\nCentre Atlantique, Temara | 10/2024 – 04/2025\n• Grundprinzipien der Webentwicklung: HTML, CSS, Bootstrap, JavaScript, PHP, MySQL\n\nDeutschkurs – Ziel B2\nRheinland Privatschule (Online) | 09/2023 – 06/2024\n• Deutschkurse bis zum stufe B2\n\nOnline-Kurs Webentwicklung & Programmierung\nLkhibra Academy, Temara | 09/2023 – 12/2023\n• Logisches Denken, Algorithmen und Python-Programmierung\n\nBaccalauréat (Hochschulreife)\nLycée Cherif el Idrissi, Rabat | 2023\n• Fachbereich Physik"
            ],
            [
                'section' => 'langues',
                'contenu' => "Arabisch: Muttersprache\nFranzösisch: Fließend\nEnglisch: Gut (Lesen technischer Dokumentationen, schriftliche Kommunikation)\nDeutsch: B1 (aktive Weiterentwicklung, gerne in Alltagskommunikation üben)"
            ],
            [
                'section' => 'personliche_daten',
                'contenu' => "Geburtsdatum: 15. Juli 2005, Rabat (Marokko)\nNationalität: Marokkanisch\nFamilienstand: Ledig\nAdresse: Groupement des Forces Auxiliaires, Rabat, Marokko\nFührerschein: Klasse B\nVerfügbarkeit: Nach Absprache, Umzug nach Deutschland möglich (Visum im beschleunigten Verfahren)"
            ],
            [
                'section' => 'interessen',
                'contenu' => "Neue Technologien und Open-Source-Projekte verfolgen · Anfänger im Programmieren mentoren · Technische Artikel, Tutorials und Developer-Blogs lesen"
            ]
        ];

        foreach ($sections as $sec) {
            CvSection::updateOrCreate(
                ['section' => $sec['section']],
                ['contenu' => $sec['contenu']]
            );
        }


        // 4. Create Sample Pending Entreprises
        Entreprise::firstOrCreate(
            ['email' => 'hr@sap-example.de'],
            [
                'nom' => 'SAP Deutschland SE',
                'directeur' => 'Schmidt',
                'telephone' => '+49 6227 747474',
                'secteur' => 'Software & Cloud Solutions',
                'texte_personnalise' => 'SAP beeindruckt mich durch seine führende Rolle bei Enterprise-Software und Cloud-Lösungen.',
                'est_envoye' => false,
            ]
        );

        Entreprise::firstOrCreate(
            ['email' => 'karriere@bmw-example.de'],
            [
                'nom' => 'BMW Group Digital',
                'directeur' => 'Müller',
                'telephone' => '+49 89 3820',
                'secteur' => 'Automotive IT & Autonomous Systems',
                'texte_personnalise' => 'Die Kombination aus innovativer Softwareentwicklung und Automobiltechnologie bei der BMW Group begeistert mich extrem.',
                'est_envoye' => false,
            ]
        );
    }
}

