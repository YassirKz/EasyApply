<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        // Default to Gemini Flash API endpoint
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->apiKey;
    }

    /**
     * Generate 4-6 personalized motivation sentences in German for a company application.
     */
    public function generatePersonalizedText(
        string $nomEntreprise,
        ?string $secteur = null,
        ?string $directeur = null,
        ?string $offreTexte = null,
        ?array $cvSections = null,
        ?string $email = null,
        ?string $telephone = null
    ): string {
        // Decode HTML entities (e.g. &amp; -> &) for clean presentation
        $nomClean = trim(htmlspecialchars_decode($nomEntreprise, ENT_QUOTES));
        $secteurClean = $secteur ? trim(htmlspecialchars_decode($secteur, ENT_QUOTES)) : '';
        $directeurClean = $directeur ? trim(htmlspecialchars_decode($directeur, ENT_QUOTES)) : '';
        $offreClean = $offreTexte ? trim(htmlspecialchars_decode($offreTexte, ENT_QUOTES)) : '';

        // Build candidate CV summary
        $cvSummary = '';
        if (!empty($cvSections)) {
            foreach ($cvSections as $sectionKey => $content) {
                if (!empty($content)) {
                    $cvSummary .= "- " . ucfirst($sectionKey) . ": " . trim($content) . "\n";
                }
            }
        }

        // If Gemini API Key is configured in .env, perform live AI call
        if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_GEMINI_API_KEY') {
            $prompt = "Du bist ein professioneller KI-Assistent in der Anwendung EasyApply für Bewerbungen in Deutschland.\n"
                . "Deine Aufgabe: Schreibe ein hochpersonalisiertes Anschreiben-Absatz (4 bis 6 Sätze) auf Deutsch.\n\n"
                . "STRIKTE REGELN:\n"
                . "1. Professioneller, hochmotivierter und überzeugender Ton.\n"
                . "2. Verknüpfe die spezifischen Fähigkeiten des Bewerbers (aus seinem Lebenslauf) direkt mit den Anforderungen der Stellenanzeige.\n"
                . "3. ZWINGENDE LESE-BESTÄTIGUNG: Erwähne mindestens EIN konkretes, spezifisches Detail aus der Stellenanzeige (z.B. Branche, Software-Typ, Ort, Aufgaben oder Technologie-Stack).\n"
                . "4. ABSOLUTES VERBOT von Anreden wie 'Sehr geehrte Damen und Herren'.\n"
                . "5. ABSOLUTES VERBOT von Grußformeln wie 'Mit freundlichen Grüßen'.\n"
                . "6. Antworte AUSSCHLIESSLICH auf Deutsch mit makelloser Grammatik.\n"
                . "7. KEINE Anführungszeichen, KEIN Einleitungstext wie 'Hier ist der Text'. Gib NUR den Fließtext des Absatzes zurück.\n"
                . "8. VERWENDE KEINE eckigen Klammern [] oder Platzhalter. Ersetze Begriffe direkt durch die echten Namen und konkreten Daten des Unternehmens.\n\n"
                . "EINGABEDATEN:\n"
                . "Unternehmensdaten:\n"
                . "- Firma: {$nomClean}\n"
                . ($secteurClean ? "- Branche/Sektor: {$secteurClean}\n" : "")
                . ($directeurClean ? "- Ansprechpartner/RH: {$directeurClean}\n" : "")
                . ($email ? "- E-Mail: {$email}\n" : "")
                . ($telephone ? "- Telefon: {$telephone}\n" : "")
                . ($offreClean ? "\nStellenanzeige (Stellenausschreibung):\n{$offreClean}\n" : "")
                . ($cvSummary ? "\nLebenslauf des Bewerbers (Yassir Kezzi):\n{$cvSummary}\n" : "");

            try {
                $response = Http::timeout(10)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($this->apiUrl, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $cleanText = trim(strip_tags(htmlspecialchars_decode($generatedText, ENT_QUOTES)));
                    if (!empty($cleanText)) {
                        return $cleanText;
                    }
                }

                Log::error("Gemini API call failed with status {$response->status()}: " . $response->body());
            } catch (\Exception $e) {
                Log::error("Gemini API Exception: " . $e->getMessage());
            }
        }

        // Fallback: Intelligent Multi-Pattern Varied Text Generator (Ensures unique texts per company)
        $secteurPhrase = $secteurClean ? "im Bereich {$secteurClean}" : "in Ihrer Branche";

        $patterns = [
            "Das innovative Produktportfolio von {$nomClean} {$secteurPhrase} begeistert mich nachhaltig. Als engagierter Fachinformatiker möchte ich meine Begeisterung für moderne Softwarearchitekturen gewinnbringend in Ihre Teams einbringen. Durch meine praktischen Erfahrungen im Full-Stack-Bereich und die Entwicklung robuster Webanwendungen bringe ich fundierte Kenntnisse mit, die optimal zu Ihren Anforderungen passen. Gemeinsam mit {$nomClean} möchte ich zukunftsfähige digitale Lösungen vorantreiben und mich kontinuierlich weiterentwickeln.",

            "Die herausragenden Entwicklungen von {$nomClean} {$secteurPhrase} bieten das ideale Umfeld für meine berufliche Spezialisierung. Ich verfolge Ihre aktuellen Projekte mit großem Interesse und bin überzeugt, mit meinen Full-Stack-Kenntnissen einen wertvollen Beitrag zu leisten. Meine praktische Erfahrung in der Konzeption und Optimierung von Datenbanken sowie modernen Frameworks ergänzt Ihr Anforderungsprofil ideal. Sehr gerne möchte ich mich in Ihre zukunftsorientierten Softwareprojekte aktiv einbringen.",

            "Als dynamischer Entwickler schätze ich den hohen Qualitätsanspruch und die technische Exzellenz von {$nomClean} außerordentlich. Die Chance, an der Weiterentwicklung Ihrer Systeme {$secteurPhrase} mitzuwirken, motiviert mich zutiefst. Aus meinen bisherigen Projekten bringe ich fundierte Kenntnisse in Frontend- und Backend-Technologien sowie agile Arbeitsweisen mit. Ich freue mich darauf, Ihr Entwicklerteam tatkräftig zu verstärken.",

            "{$nomClean} steht für zukunftsorientierte Technologie und kontinuierliches Wachstum. Meine soliden Grundlagen in Web-Technologien, Datenbankdesign und agiler Entwicklung passen perfekt zu den Anforderungsprofilen Ihrer Teams. Insbesondere die praxisnahe Umsetzung moderner Softwarelösungen begeistert mich an Ihrer Stellenausschreibung. Ich bin überzeugt, durch mein Engagement und meine schnelle Auffassungsgabe einen nachhaltigen Mehrwert für Ihr Unternehmen zu schaffen.",

            "Die Arbeit an maßgeschneiderten IT-Lösungen bei {$nomClean} fasziniert mich sehr. Ich bringe eine hohe Lernbereitschaft sowie ausgeprägte Problemlösungskompetenz mit, um Ihre Entwicklerteams {$secteurPhrase} tatkräftig zu unterstützen. Meine fundierten Kenntnisse im Bereich der modernen Anwendungsentwicklung ermöglichen mir einen raschen Einarbeitungsprozess in Ihre spezifischen Technologien. Ich freue mich darauf, mich motiviert und zielgerichtet in Ihre anstehenden Projekte einzubringen."
        ];

        // Pick deterministic yet varied pattern based on company name hash
        $index = abs(crc32($nomClean)) % count($patterns);
        return $patterns[$index];
    }

    /**
     * Extract structured company & contact information from a job offer text using Gemini AI.
     * Returns an array with keys: firma, email, direktor, secteur.
     */
    public function extractJobData(string $jobOfferText): array
    {
        $cleanOffer = trim(htmlspecialchars_decode($jobOfferText, ENT_QUOTES));

        if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_GEMINI_API_KEY') {
            $prompt = "Du bist ein Assistent für Bewerbungsmanagement. Analysiere den folgenden Text einer Stellenanzeige.\n\n"
                . "Extrahiere daraus diese 4 Informationen:\n"
                . "1. Firmenname (Name des Unternehmens)\n"
                . "2. Kontakt-E-Mail (Bewerbungs-E-Mail oder generische Kontakt-E-Mail)\n"
                . "3. Ansprechpartner (Name des Recruiters / Chefs, falls genannt, sonst \"nicht genannt\")\n"
                . "4. Branche (Sektor, z.B. \"Automotive\", \"Software\", \"Beratung\")\n\n"
                . "Gib die Antwort NUR als JSON-Objekt zurück, ohne Erklärungen.\n"
                . "Format:\n"
                . "{\n"
                . "  \"firma\": \"...\",\n"
                . "  \"email\": \"...\",\n"
                . "  \"direktor\": \"...\",\n"
                . "  \"secteur\": \"...\"\n"
                . "}\n\n"
                . "Text der Stellenanzeige:\n"
                . $cleanOffer;

            try {
                $response = Http::timeout(10)->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($this->apiUrl, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                    // Clean JSON string (strip markdown code block syntax if present like ```json ... ```)
                    $rawText = preg_replace('/^```(?:json)?/i', '', trim($rawText));
                    $rawText = preg_replace('/```$/', '', trim($rawText));

                    $json = json_decode(trim($rawText), true);

                    if (is_array($json)) {
                        return [
                            'firma'    => trim(htmlspecialchars_decode(strip_tags($json['firma'] ?? ''), ENT_QUOTES)),
                            'email'    => trim(htmlspecialchars_decode(strip_tags($json['email'] ?? ''), ENT_QUOTES)),
                            'direktor' => trim(htmlspecialchars_decode(strip_tags($json['direktor'] ?? ''), ENT_QUOTES)),
                            'secteur'  => trim(htmlspecialchars_decode(strip_tags($json['secteur'] ?? ''), ENT_QUOTES)),
                            // Support phone number if the model provides one (key can be 'telefon' or 'telephone')
                            'telefon'  => trim(htmlspecialchars_decode(strip_tags($json['telefon'] ?? $json['telephone'] ?? ''), ENT_QUOTES)),
                        ];
                    }
                }

                Log::error("Gemini Extraction failed with status {$response->status()}: " . $response->body());
            } catch (\Exception $e) {
                Log::error("Gemini Extraction Exception: " . $e->getMessage());
            }
        }

        // Fallback RegEx & Heuristics Extractor when API Key is missing or unavailable
        return $this->fallbackExtractJobData($cleanOffer);
    }

    /**
     * Fallback heuristic parser for job offers (testing / offline mode).
     */
    protected function fallbackExtractJobData(string $text): array
    {
        // Extract Email
        $email = '';
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            $email = $matches[0];
        }

        // Extract Telefon (simple international-friendly pattern)
        $telefon = '';
        if (preg_match('/(\+?\d[\d\s\/\-\(\)]{5,}\d)/', $text, $pm)) {
            $telefon = trim($pm[0]);
        }

        // Extract Company Name (looks for GmbH, AG, SE, KG, Inc, UG or capitalized company names)
        $firma = '';
        if (preg_match('/(?:bei|von|für|Unternehmen|Firma|Arbeitgeber)\s+([A-Z0-9\&\s\-\_]{3,30}(?:GmbH|AG|SE|KG|UG|Inc)?)/u', $text, $matches)) {
            $firma = trim($matches[1]);
        } elseif (preg_match('/([A-Z0-9\&\s\-\_]{2,25}\s+(?:GmbH|AG|SE|KG|UG|Inc))/u', $text, $matches)) {
            $firma = trim($matches[1]);
        }

        if (empty($firma)) {
            if (!empty($email)) {
                $parts = explode('@', $email);
                $domain = explode('.', $parts[1] ?? '')[0] ?? 'Unternehmen';
                $firma = ucwords(str_replace(['-', '_'], ' ', $domain)) . ' GmbH';
            } else {
                $firma = 'Unternehmen Allemand';
            }
        }

        // Extract Director / Ansprechpartner
        $direktor = 'nicht genannt';
        if (preg_match('/(?:Herr|Frau)\s+([A-Z][a-zäöüß]+(?:\s+[A-Z][a-zäöüß]+)?)/u', $text, $matches)) {
            $direktor = trim($matches[0]);
        }

        // Extract Sector
        $secteur = 'IT & Softwareentwicklung';
        $sectorPatterns = [
            'Automotive' => '/(Automotive|Fahrzeugbau|Automobil|E-Mobility)/i',
            'Softwareentwicklung' => '/(Software|IT-Dienstleistung|Digitalisierung|Cloud|SaaS|Webentwicklung)/i',
            'Maschinenbau & Industrie' => '/(Maschinenbau|Industrie|Fertigung|Automation|Elektrotechnik)/i',
            'Beratung & Consulting' => '/(Beratung|Consulting|Unternehmensberatung)/i',
            'Finanzen & Banken' => '/(Finanz|Bank|Insurance|Versicherung|Fintech)/i',
            'E-Commerce & Handel' => '/(E-Commerce|Handel|Retail|Shop)/i',
            'Gesundheitswesen & Medizin' => '/(Medizin|Pharma|Gesundheit|Healthcare)/i',
            'Logistik & Transport' => '/(Logistik|Transport|Supply Chain)/i',
        ];

        foreach ($sectorPatterns as $sectorName => $pattern) {
            if (preg_match($pattern, $text)) {
                $secteur = $sectorName;
                break;
            }
        }

        return [
            'firma'    => trim(htmlspecialchars_decode(strip_tags($firma), ENT_QUOTES)),
            'email'    => trim(htmlspecialchars_decode(strip_tags($email ?: 'info@company.de'), ENT_QUOTES)),
            'direktor' => trim(htmlspecialchars_decode(strip_tags($direktor), ENT_QUOTES)),
            'secteur'  => trim(htmlspecialchars_decode(strip_tags($secteur), ENT_QUOTES)),
            'telefon'  => trim(htmlspecialchars_decode(strip_tags($telefon), ENT_QUOTES)),
        ];
    }
}
