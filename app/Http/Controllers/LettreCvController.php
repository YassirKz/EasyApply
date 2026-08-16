<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use App\Models\CvSection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LettreCvController extends Controller
{
    /**
     * Get the per-user documents storage path.
     */
    public static function getDocumentsPath(?int $userId = null): string
    {
        $userId = $userId ?? Auth::id();
        return storage_path("app/documents/user_{$userId}/anlagen.pdf");
    }

    /**
     * Get the per-user profile photo public path.
     */
    public static function getPhotoPath(?int $userId = null): string
    {
        $userId = $userId ?? Auth::id();
        return public_path("images/profile_photo_user_{$userId}.jpg");
    }

    // ─── Lettre de motivation ──────────────────────────────────────────────

    /**
     * View and edit motivation letter template.
     */
    public function editLettre()
    {
        // Global Scope auto-filters to Auth::id()
        $parametre = Parametre::where('cle', 'modele_lettre')->first();
        $lettre = $parametre ? $parametre->valeur : '';

        return view('lettre_cv.lettre', compact('lettre'));
    }

    /**
     * Update motivation letter template.
     */
    public function updateLettre(Request $request)
    {
        $request->validate([
            'valeur' => 'required|string',
        ]);

        // Global Scope + model booted() ensure this is scoped to Auth::id()
        Parametre::updateOrCreate(
            ['cle' => 'modele_lettre'],
            ['valeur' => $request->input('valeur')]
        );

        return redirect()->back()->with('success', 'Modèle de lettre mis à jour avec succès.');
    }

    // ─── CV ────────────────────────────────────────────────────────────────

    /**
     * View and edit CV sections and attached documents.
     */
    public function editCv()
    {
        // Global Scope auto-filters to Auth::id()
        $sections = CvSection::all()->keyBy('section');
        $parametres = Parametre::all()->keyBy('cle');

        $docPath = self::getDocumentsPath();
        $hasDocuments = file_exists($docPath);
        $documentsSizeFormatted = '';

        if ($hasDocuments) {
            $bytes = filesize($docPath);
            $documentsSizeFormatted = round($bytes / (1024 * 1024), 2) . ' Mo';
        }

        return view('lettre_cv.cv', compact('sections', 'parametres', 'hasDocuments', 'documentsSizeFormatted'));
    }

    /**
     * Update CV sections and profile photo.
     */
    public function updateCv(Request $request)
    {
        $data = $request->validate([
            'cv_subtitle'       => 'nullable|string|max:255',
            'cv_phone'          => 'nullable|string|max:100',
            'cv_links'          => 'nullable|string|max:550',
            'profil'            => 'nullable|string',
            'competences'       => 'nullable|string',
            'praktikum'         => 'nullable|string',
            'projekterfahrung'  => 'nullable|string',
            'ausbildung'        => 'nullable|string',
            'langues'           => 'nullable|string',
            'personliche_daten' => 'nullable|string',
            'interessen'        => 'nullable|string',
            'photo'             => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        // Handle Header parameters (Subtitle, Phone, Links)
        foreach (['cv_subtitle', 'cv_phone', 'cv_links'] as $headerKey) {
            if (isset($data[$headerKey])) {
                Parametre::updateOrCreate(
                    ['cle' => $headerKey],
                    ['valeur' => $data[$headerKey]]
                );
                unset($data[$headerKey]);
            }
        }

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $imagesDir = public_path('images');
            if (!file_exists($imagesDir)) {
                mkdir($imagesDir, 0755, true);
            }
            // Store per-user photo — remove old one first
            $photoPath = self::getPhotoPath();
            if (file_exists($photoPath)) {
                @unlink($photoPath);
            }
            $userId = Auth::id();
            $photo->move($imagesDir, "profile_photo_user_{$userId}.jpg");
        }

        unset($data['photo']);

        // Global Scope + model booted() ensure each section is scoped to Auth::id()
        foreach ($data as $sectionKey => $contenu) {
            CvSection::updateOrCreate(
                ['section' => $sectionKey],
                ['contenu' => $contenu]
            );
        }

        return redirect()->back()->with('success', 'Mon CV et mes informations d\'en-tête ont été mis à jour avec succès.');
    }

    // ─── Documents (Anlagen PDF) ───────────────────────────────────────────

    /**
     * Upload custom PDF document (Anlagen) — stored per user.
     */
    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:15360',
        ], [
            'document.required' => 'Veuillez sélectionner un fichier PDF.',
            'document.mimes'    => 'Le fichier doit être obligatoirement au format PDF.',
            'document.max'      => 'La taille du fichier PDF ne doit pas dépasser 15 Mo.',
        ]);

        $file = $request->file('document');
        $userId = Auth::id();
        $docsDir = storage_path("app/documents/user_{$userId}");
        if (!file_exists($docsDir)) {
            mkdir($docsDir, 0755, true);
        }

        $file->move($docsDir, 'anlagen.pdf');

        return redirect()->route('cv.edit')->with('success', '📄 Document (Anlagen) téléversé et enregistré avec succès ! Il sera automatiquement joint à vos candidatures.');
    }

    /**
     * Download uploaded custom PDF document.
     */
    public function downloadDocuments()
    {
        $docPath = self::getDocumentsPath();
        if (!file_exists($docPath)) {
            return redirect()->route('cv.edit')->with('error', 'Aucun document téléversé.');
        }

        return response()->download($docPath, 'Anlagen.pdf');
    }

    /**
     * Delete uploaded custom PDF document.
     */
    public function deleteDocuments()
    {
        $docPath = self::getDocumentsPath();
        if (file_exists($docPath)) {
            @unlink($docPath);
        }

        return redirect()->route('cv.edit')->with('success', 'Document supprimé avec succès.');
    }

    // ─── PDF Preview ───────────────────────────────────────────────────────

    /**
     * Download or preview PDF CV.
     */
    public function previewPdf(Request $request)
    {
        $user = Auth::user();
        // Global Scope auto-filters to Auth::id()
        $cvSections = CvSection::all()->keyBy('section');
        $pdf = Pdf::loadView('cv.pdf_template', compact('cvSections', 'user'));

        if ($request->has('download')) {
            $slugName = \Illuminate\Support\Str::slug($user->name ?? 'lebenslauf');
            return $pdf->download("lebenslauf_{$slugName}.pdf");
        }

        return $pdf->stream('lebenslauf.pdf');
    }
}
