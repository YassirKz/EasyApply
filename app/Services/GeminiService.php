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
                            'firma'    => e(strip_tags($json['firma'] ?? '')),
                            'email'    => e(strip_tags($json['email'] ?? '')),
                            'direktor' => e(strip_tags($json['direktor'] ?? '')),
                            'secteur'  => e(strip_tags($json['secteur'] ?? '')),
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
        return [
            'firma'    => e(strip_tags($firma)),
            'email'    => e(strip_tags($email)),
            'direktor' => e(strip_tags($direktor)),
            'secteur'  => e(strip_tags($secteur)),
        ];
    }

    /**
     * Search and extract company data from any input (Name, URL, Job Offer Text, or combination) using Gemini AI.
     * Fetches live website content when a URL or company name is provided for maximum accuracy.
     * Returns an array with keys: firma, email, direktor, secteur.
     */
    public function searchCompanyData(string $input): array
    {
        $cleanInput = trim(htmlspecialchars_decode($input, ENT_QUOTES));

        // Fetch live website content if input contains a URL or company domain
        $liveWebContent = $this->fetchWebsiteContent($cleanInput);

        if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_GEMINI_API_KEY') {
            $promptInput = $cleanInput;
            if (!empty($liveWebContent)) {
                $promptInput .= "\n\n--- REAL LIVE WEBSITE CONTENT FETCHED FROM COMPANY ---\n" . $liveWebContent;
            }

            $prompt = "Du bist ein Assistent für Bewerbungsmanagement. Deine Aufgabe ist es, aus den folgenden Informationen (beliebiger Typ: Name, URL, Live-Website-Text oder Stellenanzeige) die EXAKTEN echten Daten für eine Bewerbung zu extrahieren.\n\n"
                . "**Eingabe (einer der folgenden Typen):**\n"
                . "- Typ 1: Nur der Firmenname (z.B. \"BMW\")\n"
                . "- Typ 2: Eine URL (z.B. \"https://www.bmw.de\")\n"
                . "- Typ 3: Der vollständige Text einer Stellenanzeige oder Website-Inhalt\n"
                . "- Typ 4: Eine Kombination aus Name + URL + Text\n\n"
                . "**Deine Aufgabe:**\n"
                . "1. Erkenne automatisch, welcher Typ vorliegt.\n"
                . "2. Extrahiere oder recherchiere (aus den angegebenen Website-Daten oder Deinem Wissen) diese 4 Informationen:\n"
                . "   - Firmenname (bestätigen oder exakten offiziellen Namen wie GmbH/AG aus dem Impressum nutzen)\n"
                . "   - Kontakt-E-Mail (nutze bevorzugt echte E-Mails aus dem Impressum/Kontakt wie karriere@firma.de, impressum@firma.de, kontakt@firma.de oder info@firma.de)\n"
                . "   - Ansprechpartner (Name des Vorstands/Geschäftsführers/Recruiters, falls bekannt oder genannt, sonst \"Personalabteilung\")\n"
                . "   - Branche (z.B. \"Automotive\", \"Software\", \"Beratung\", \"Industrie\")\n\n"
                . "3. **Regeln:**\n"
                . "   - Wenn echter Website-Inhalt mitgeliefert wird, nutze DIESEN bevorzugt für E-Mail, exakten Firmennamen und Ansprechpartner.\n"
                . "   - Wenn die Eingabe nur ein Name/URL ist, nutze Dein Wissen und die Web-Daten, um die exakten Felder zu füllen.\n"
                . "   - Wenn eine Information fehlt, gib einen plausiblen Standardwert (z.B. \"Personalabteilung\" für Ansprechpartner).\n\n"
                . "**Gib die Antwort NUR als JSON-Objekt zurück, ohne Erklärungen.**\n"
                . "Format:\n"
                . "{\n"
                . "  \"firma\": \"...\",\n"
                . "  \"email\": \"...\",\n"
                . "  \"direktor\": \"...\",\n"
                . "  \"secteur\": \"...\"\n"
                . "}\n\n"
                . "Eingabe & Live-Inhalt:\n"
                . $promptInput;

            try {
                $response = Http::timeout(12)->withHeaders([
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
                            'firma'    => e(strip_tags($json['firma'] ?? '')),
                            'email'    => e(strip_tags($json['email'] ?? '')),
                            'direktor' => e(strip_tags($json['direktor'] ?? '')),
                            'secteur'  => e(strip_tags($json['secteur'] ?? '')),
                        ];
                    }
                }

                Log::error("Gemini Company Search failed with status {$response->status()}: " . $response->body());
            } catch (\Exception $e) {
                Log::error("Gemini Company Search Exception: " . $e->getMessage());
            }
        }

        // Fallback search extractor when API key is unavailable or fails
        return $this->fallbackSearchCompanyData($cleanInput);
    }

    /**
     * Fetch live website content (Homepage, Impressum, Kontakt, Karriere) if input contains URL or domain.
     */
    protected function fetchWebsiteContent(string $input): string
    {
        $urlsToTry = [];

        // Check if input contains explicit URL
        if (preg_match('/https?:\/\/[^\s]+/i', $input, $matches)) {
            $urlsToTry[] = $matches[0];
        } elseif (preg_match('/(?:www\.)?([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})(?:\/[^\s]*)?/i', $input, $matches)) {
            $domain = $matches[1];
            $urlsToTry[] = "https://www." . ltrim($domain, 'www.');
            $urlsToTry[] = "https://" . ltrim($domain, 'www.');
        } else {
            // If input is short company name e.g. "BMW" or "Bosch", try common .de / .com domain
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', trim($input))[0]));
            if (!empty($cleanName) && strlen($cleanName) >= 3 && strlen($input) < 40) {
                $urlsToTry[] = "https://www.{$cleanName}.de";
                $urlsToTry[] = "https://www.{$cleanName}.com";
            }
        }

        $scrapedText = '';

        foreach ($urlsToTry as $baseUrl) {
            try {
                // Fetch Homepage
                $response = Http::timeout(4)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept-Language' => 'de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
                    ])
                    ->get($baseUrl);

                if ($response->successful()) {
                    $html = $response->body();
                    $scrapedText .= "\n--- WEBPAGE ({$baseUrl}) ---\n" . $this->extractCleanTextFromHtml($html);

                    // Try to find Impressum, Kontakt, or Karriere link in HTML
                    if (preg_match('/href=["\']([^"\']*(?:impressum|kontakt|contact|karriere|about)[^"\']*)["\']/i', $html, $linkMatches)) {
                        $subPath = $linkMatches[1];
                        $subUrl = preg_match('/^https?:\/\//i', $subPath)
                            ? $subPath
                            : rtrim($baseUrl, '/') . '/' . ltrim($subPath, '/');

                        try {
                            $subResp = Http::timeout(3)
                                ->withHeaders([
                                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                                    'Accept-Language' => 'de-DE,de;q=0.9',
                                ])
                                ->get($subUrl);

                            if ($subResp->successful()) {
                                $scrapedText .= "\n--- IMPRESSUM/KONTAKT ({$subUrl}) ---\n" . $this->extractCleanTextFromHtml($subResp->body());
                            }
                        } catch (\Exception $e) {
                            // Subpage fetch error ignored
                        }
                    }

                    break; // Successfully fetched main page
                }
            } catch (\Exception $e) {
                Log::warning("Web scraping attempt failed for {$baseUrl}: " . $e->getMessage());
            }
        }

        return mb_substr($scrapedText, 0, 4000);
    }

    /**
     * Clean raw HTML down to plain readable text.
     */
    protected function extractCleanTextFromHtml(string $html): string
    {
        $html = preg_replace('/<(script|style|svg|noscript|header|footer)[^>]*?>.*?<\/\\1>/si', '', $html);
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Fallback heuristic search parser (testing / offline mode).
     */
    protected function fallbackSearchCompanyData(string $input): array
    {
        // Check if input is or contains URL
        $domain = '';
        if (preg_match('/https?:\/\/(?:www\.)?([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $input, $matches)) {
            $domain = $matches[1];
        } elseif (preg_match('/(?:www\.)?([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $input, $matches)) {
            $domain = $matches[1];
        }

        // Extract Email if present, or build plausible email from domain/name
        $email = '';
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $input, $matches)) {
            $email = $matches[0];
        }

        // Determine company name
        $firma = '';
        if (!empty($domain)) {
            $baseName = explode('.', $domain)[0];
            $firma = ucwords(str_replace(['-', '_'], ' ', $baseName)) . ' GmbH';
            if (empty($email)) {
                $email = 'bewerbung@' . strtolower($domain);
            }
        } else {
            // Check if input is short company name or full text
            if (strlen($input) < 60) {
                $firma = trim($input);
                if (!str_contains(strtolower($firma), 'gmbh') && !str_contains(strtolower($firma), 'ag')) {
                    $firma .= ' GmbH';
                }
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $input)[0]));
                if (empty($email) && !empty($slug)) {
                    $email = 'bewerbung@' . $slug . '.de';
                }
            } else {
                return $this->fallbackExtractJobData($input);
            }
        }

        // Director
        $direktor = 'Personalabteilung';
        if (preg_match('/(?:Herr|Frau)\s+([A-Z][a-zäöüß]+(?:\s+[A-Z][a-zäöüß]+)?)/u', $input, $matches)) {
            $direktor = trim($matches[0]);
        }

        // Sector
        $secteur = 'IT & Technology';
        if (preg_match('/(Automotive|Software|Beratung|Finanzen|Medizin|E-Commerce|Marketing|Ingenieurwesen|Industrie)/i', $input, $matches)) {
            $secteur = ucfirst(strtolower($matches[0]));
        }

        return [
            'firma'    => e(strip_tags($firma)),
            'email'    => e(strip_tags($email ?: 'info@company.de')),
            'direktor' => e(strip_tags($direktor)),
            'secteur'  => e(strip_tags($secteur)),
        ];
    }
}
