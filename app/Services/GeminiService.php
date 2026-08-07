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
     * Generate 3-4 personalized sentences in German for a company application.
     */
    public function generatePersonalizedText(string $nomEntreprise, ?string $secteur = null, ?string $directeur = null): string
    {
        // Decode HTML entities (e.g. &amp; -> &) for clean presentation
        $nomClean = trim(htmlspecialchars_decode($nomEntreprise, ENT_QUOTES));
        $secteurClean = $secteur ? trim(htmlspecialchars_decode($secteur, ENT_QUOTES)) : '';
        $directeurClean = $directeur ? trim(htmlspecialchars_decode($directeur, ENT_QUOTES)) : '';

        // If Gemini API Key is configured in .env, perform live AI call
        if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_GEMINI_API_KEY') {
            $prompt = "Du bist ein professioneller Bewerbungs-Assistent für Fachinformatiker Anwendungsentwicklung in Deutschland. "
                . "Schreibe einen kurzen, überzeugenden und hochprofessionellen Absatz (3 bis 4 Sätze) auf Deutsch für ein Anschreiben an das Unternehmen '{$nomClean}'"
                . ($secteurClean ? " im Bereich '{$secteurClean}'" : "")
                . ($directeurClean ? " (Ansprechpartner: Herr/Frau {$directeurClean})" : "") . ". "
                . "Erkläre kurz, warum der Bewerber von den Projekten und Technologien des Unternehmens begeistert ist. "
                . "Verwende KEINE Platzhalter, kein HTML, keine Anrede, nur den Fließtext des Absatzes.";

            try {
                $response = Http::timeout(5)->withHeaders([
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
            "Das innovative Produktportfolio von {$nomClean} {$secteurPhrase} begeistert mich nachhaltig. Als engagierter Fachinformatiker möchte ich meine Begeisterung für moderne Softwarearchitekturen gewinnbringend in Ihre Teams einbringen. Gemeinsam mit {$nomClean} möchte ich zukunftsfähige digitale Lösungen vorantreiben.",
            
            "Die herausragenden Entwicklungen von {$nomClean} {$secteurPhrase} bieten das ideale Umfeld für meine berufliche Spezialisierung. Ich verfolge Ihre aktuellen Projekte mit großem Interesse und bin überzeugt, mit meinen Full-Stack-Kenntnissen einen wertvollen Beitrag zu leisten.",
            
            "Als dynamischer Entwickler schätze ich den hohen Qualitätsanspruch und die technische Exzellenz von {$nomClean} außerordentlich. Die Chance, an der Weiterentwicklung Ihrer Systeme {$secteurPhrase} mitzuwirken, motiviert mich zutiefst.",
            
            "{$nomClean} steht für zukunftsorientierte Technologie und kontinuierliches Wachstum. Meine soliden Grundlagen in Web-Technologien, Datenbankdesign und agiler Entwicklung passen perfekt zu den Anforderungsprofilen Ihrer Teams.",
            
            "Die Arbeit an maßgeschneiderten IT-Lösungen bei {$nomClean} fasziniert mich sehr. Ich bringe eine hohe Lernbereitschaft sowie ausgeprägte Problemlösungskompetenz mit, um Ihre Entwicklerteams {$secteurPhrase} tatkräftig zu unterstützen."
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
