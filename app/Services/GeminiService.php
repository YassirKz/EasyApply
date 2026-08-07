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
}
