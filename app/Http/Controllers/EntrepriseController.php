<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EntrepriseController extends Controller
{
    /**
     * Display a listing of the entreprises.
     */
    public function index(Request $request)
    {
        $query = Entreprise::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('secteur', 'like', "%{$search}%")
                  ->orWhere('directeur', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('statut')) {
            if ($request->input('statut') === 'attente') {
                $query->where('est_envoye', false);
            } elseif ($request->input('statut') === 'envoye') {
                $query->where('est_envoye', true);
            } elseif ($request->input('statut') === 'relance') {
                $query->where('est_envoye', true)
                      ->where('date_envoi', '<=', now()->subDays(15));
            }
        }

        $entreprises = $query->orderBy('created_at', 'desc')->paginate(15);

        $pendingCount = Entreprise::where('est_envoye', false)
            ->where(function ($q) {
                $q->whereNull('programmation_envoi')
                  ->orWhere('programmation_envoi', '<=', now());
            })
            ->count();

        $scheduledCount = Entreprise::where('est_envoye', false)
            ->where('programmation_envoi', '>', now())
            ->count();

        $sentCount = Entreprise::where('est_envoye', true)->count();

        $relanceCount = Entreprise::where('est_envoye', true)
            ->where('date_envoi', '<=', now()->subDays(15))
            ->count();

        return view('entreprises.index', compact('entreprises', 'pendingCount', 'scheduledCount', 'sentCount', 'relanceCount'));
    }

    /**
     * Store a newly created entreprise in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:entreprises,email',
            'directeur' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'secteur' => 'nullable|string|max:255',
            'texte_personnalise' => 'nullable|string',
        ]);

        // Security cleaning (strip_tags/e)
        $validated['nom'] = e(strip_tags($validated['nom']));
        $validated['directeur'] = e(strip_tags($validated['directeur']));
        if (isset($validated['telephone'])) $validated['telephone'] = e(strip_tags($validated['telephone']));
        if (isset($validated['secteur'])) $validated['secteur'] = e(strip_tags($validated['secteur']));
        if (isset($validated['texte_personnalise'])) $validated['texte_personnalise'] = e(strip_tags($validated['texte_personnalise']));

        Entreprise::create($validated);

        return redirect()->route('entreprises.index')->with('success', 'Entreprise ajoutée avec succès.');
    }

    /**
     * Update the specified entreprise in storage.
     */
    public function update(Request $request, Entreprise $entreprise)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:entreprises,email,' . $entreprise->id,
            'directeur' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'secteur' => 'nullable|string|max:255',
            'texte_personnalise' => 'nullable|string',
        ]);

        $validated['nom'] = e(strip_tags($validated['nom']));
        $validated['directeur'] = e(strip_tags($validated['directeur']));
        if (isset($validated['telephone'])) $validated['telephone'] = e(strip_tags($validated['telephone']));
        if (isset($validated['secteur'])) $validated['secteur'] = e(strip_tags($validated['secteur']));
        if (isset($validated['texte_personnalise'])) $validated['texte_personnalise'] = e(strip_tags($validated['texte_personnalise']));

        $entreprise->update($validated);

        return redirect()->route('entreprises.index')->with('success', 'Entreprise mise à jour avec succès.');
    }

    /**
     * Remove the specified entreprise from storage.
     */
    public function destroy(Entreprise $entreprise)
    {
        $entreprise->delete();
        return redirect()->route('entreprises.index')->with('success', 'Entreprise supprimée avec succès.');
    }

    /**
     * Remove multiple specified entreprises from storage.
     */
    public function destroyBatch(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:entreprises,id',
        ]);

        $count = count($validated['ids']);
        Entreprise::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('entreprises.index')->with('success', "{$count} entreprise(s) supprimée(s) avec succès.");
    }

    /**
     * Remove all entreprises from storage.
     */
    public function destroyAll()
    {
        $count = Entreprise::count();
        Entreprise::query()->delete();
        return redirect()->route('entreprises.index')->with('success', "Toutes les {$count} entreprise(s) ont été supprimées avec succès.");
    }


    /**
     * Return a single entreprise as JSON (for edit modal AJAX).
     */
    public function showJson(Entreprise $entreprise)
    {
        return response()->json([
            'id'                 => $entreprise->id,
            'nom'                => htmlspecialchars_decode($entreprise->nom, ENT_QUOTES),
            'email'              => $entreprise->email,
            'directeur'          => htmlspecialchars_decode($entreprise->directeur ?? '', ENT_QUOTES),
            'telephone'          => htmlspecialchars_decode($entreprise->telephone ?? '', ENT_QUOTES),
            'secteur'            => htmlspecialchars_decode($entreprise->secteur ?? '', ENT_QUOTES),
            'texte_personnalise' => htmlspecialchars_decode($entreprise->texte_personnalise ?? '', ENT_QUOTES),
            'est_envoye'         => $entreprise->est_envoye,
        ]);
    }

    /**
     * Generate AI text via Google Gemini API for an entreprise.
     */
    public function generateAi(Entreprise $entreprise, GeminiService $geminiService)
    {
        $aiText = $geminiService->generatePersonalizedText(
            $entreprise->nom,
            $entreprise->secteur,
            $entreprise->directeur
        );

        $entreprise->update([
            'texte_personnalise' => $aiText
        ]);

        return response()->json([
            'success' => true,
            'texte_personnalise' => $aiText
        ]);
    }

    /**
     * Generate AI text for ALL entreprises in the database.
     */
    public function generateAiAll(GeminiService $geminiService)
    {
        // Prevent PHP execution timeout for bulk HTTP API calls
        set_time_limit(300);

        $entreprises = Entreprise::all();
        $count = 0;

        foreach ($entreprises as $entreprise) {
            $aiText = $geminiService->generatePersonalizedText(
                $entreprise->nom,
                $entreprise->secteur,
                $entreprise->directeur
            );

            $entreprise->update([
                'texte_personnalise' => $aiText
            ]);

            $count++;
        }

        return redirect()->route('entreprises.index')->with('success', "Textes IA générés avec succès pour les {$count} entreprise(s) !");
    }



    /**
     * Import entreprises from CSV / Excel file with automatic delimiter detection,
     * German/French header matching, and smart fallback.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $content = file_get_contents($path);
        // Remove UTF-8 BOM if present
        $content = preg_replace('/^[\x{EF}\x{BB}\x{BF}]/u', '', $content);
        
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines)) {
            return redirect()->route('entreprises.index')->with('errors', 'Le fichier importé est vide.');
        }

        // Detect delimiter by checking first line
        $firstLine = $lines[0];
        $delimiter = ',';
        $delimiters = [';' => substr_count($firstLine, ';'), ',' => substr_count($firstLine, ','), "\t" => substr_count($firstLine, "\t")];
        arsort($delimiters);
        $detectedDelimiter = key($delimiters);
        if ($delimiters[$detectedDelimiter] > 0) {
            $delimiter = $detectedDelimiter;
        }

        // Parse header line
        $rawHeader = str_getcsv($firstLine, $delimiter);
        $headerClean = array_map(function($h) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', $h)));
        }, $rawHeader);

        $importedCount = 0;
        $updatedCount = 0;

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $data = str_getcsv($line, $delimiter);
            $row = [];
            foreach ($headerClean as $index => $colName) {
                $row[$colName] = $data[$index] ?? '';
            }

            // Precise field matching for FULLNAME, PHONE, E-MAIL, GMBH NAME, etc.
            $email = '';
            $nom = '';
            $directeur = '';
            $telephone = '';
            $secteur = '';

            foreach ($row as $key => $val) {
                $val = trim($val);
                if (empty($val)) continue;

                // Match Email (E-MAIL, EMAIL, MAIL)
                if (empty($email) && (str_contains($key, 'mail') || filter_var($val, FILTER_VALIDATE_EMAIL))) {
                    if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $email = $val;
                    }
                }
                // Match Company Name (GMBH NAME, GMBH, FIRMA, FIRMENNAME, COMPANY, ENTREPRISE, NOM_ENTREPRISE)
                elseif (empty($nom) && (str_contains($key, 'gmbh') || str_contains($key, 'firma') || str_contains($key, 'entreprise') || str_contains($key, 'company') || str_contains($key, 'nom_entreprise') || str_contains($key, 'company_name'))) {
                    if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $nom = $val;
                    }
                }
                // Match Director / Contact Name (FULLNAME, FULL_NAME, DIRECTEUR, RESPONSABLE, ANSPRECHPARTNER, CONTACT)
                elseif (empty($directeur) && (str_contains($key, 'fullname') || str_contains($key, 'full_name') || str_contains($key, 'directeur') || str_contains($key, 'responsable') || str_contains($key, 'contact') || str_contains($key, 'ansprechpartner') || str_contains($key, 'manager'))) {
                    $directeur = $val;
                }
                // Match Phone (PHONE, TEL, TELEFON, HANDY)
                elseif (empty($telephone) && (str_contains($key, 'phone') || str_contains($key, 'tel') || str_contains($key, 'telefon') || str_contains($key, 'handy'))) {
                    $telephone = $val;
                }
                // Match Sector (SECTEUR, BRANCHE, INDUSTRY, SECTOR)
                elseif (empty($secteur) && (str_contains($key, 'secteur') || str_contains($key, 'branche') || str_contains($key, 'industry') || str_contains($key, 'sector'))) {
                    $secteur = $val;
                }
            }

            // Fallback for Company Name if not matched by GMBH/Firma: look for general name
            if (empty($nom)) {
                foreach ($row as $key => $val) {
                    $val = trim($val);
                    if (empty($val) || filter_var($val, FILTER_VALIDATE_EMAIL)) continue;
                    if ((str_contains($key, 'nom') || str_contains($key, 'name')) && !str_contains($key, 'fullname') && !str_contains($key, 'full_name') && $val !== $directeur) {
                        $nom = $val;
                        break;
                    }
                }
            }


            // Fallback: If email empty, check all column values for an email string
            if (empty($email)) {
                foreach ($data as $cell) {
                    $cell = trim($cell);
                    if (filter_var($cell, FILTER_VALIDATE_EMAIL)) {
                        $email = $cell;
                        break;
                    }
                }
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Smart fallback for company name from email domain if missing or generic
            if (empty($nom) || strtolower($nom) === 'entreprise allemande') {
                $emailParts = explode('@', $email);
                $domainHost = $emailParts[1] ?? '';
                $domainName = explode('.', $domainHost)[0] ?? 'Entreprise';
                $nom = ucwords(str_replace(['-', '_'], ' ', $domainName));
            }

            if (empty($directeur)) {
                $directeur = 'Responsable Recrutement';
            }

            // Clean inputs (XSS security)
            $nom = e(strip_tags($nom));
            $directeur = e(strip_tags($directeur));
            $telephone = e(strip_tags($telephone));
            $secteur = e(strip_tags($secteur));

            // Upsert option A
            $existing = Entreprise::where('email', $email)->first();
            if ($existing) {
                $existing->update([
                    'nom' => $nom ?: $existing->nom,
                    'directeur' => $directeur ?: $existing->directeur,
                    'telephone' => $telephone ?: $existing->telephone,
                    'secteur' => $secteur ?: $existing->secteur,
                ]);
                $updatedCount++;
            } else {
                Entreprise::create([
                    'nom' => $nom,
                    'email' => $email,
                    'directeur' => $directeur,
                    'telephone' => $telephone,
                    'secteur' => $secteur,
                    'est_envoye' => false,
                ]);
                $importedCount++;
            }
        }

        return redirect()->route('entreprises.index')->with('success', "Importation réussie : {$importedCount} nouvelle(s) entreprise(s) ajoutée(s), {$updatedCount} mise(s) à jour.");
    }

}
