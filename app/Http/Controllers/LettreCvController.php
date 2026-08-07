<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use App\Models\CvSection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LettreCvController extends Controller
{
    /**
     * View and edit motivation letter template.
     */
    public function editLettre()
    {
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

        // Clean user input
        $valeurNettoyee = strip_tags($request->input('valeur'), '<br><p>');

        Parametre::updateOrCreate(
            ['cle' => 'modele_lettre'],
            ['valeur' => $request->input('valeur')]
        );

        return redirect()->back()->with('success', 'Modèle de lettre mis à jour avec succès.');
    }

    public static function getDocumentsPath(): string
    {
        return storage_path('app/documents/anlagen.pdf');
    }

    /**
     * View and edit CV sections and attached documents.
     */
    public function editCv()
    {
        $sections = CvSection::all()->keyBy('section');

        $docPath = self::getDocumentsPath();
        $hasDocuments = file_exists($docPath);
        $documentsSizeFormatted = '';

        if ($hasDocuments) {
            $bytes = filesize($docPath);
            $documentsSizeFormatted = round($bytes / (1024 * 1024), 2) . ' Mo';
        }

        return view('lettre_cv.cv', compact('sections', 'hasDocuments', 'documentsSizeFormatted'));
    }

    /**
     * Update CV sections and profile photo.
     */
    public function updateCv(Request $request)
    {
        $data = $request->validate([
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

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $imagesDir = public_path('images');
            if (!file_exists($imagesDir)) {
                mkdir($imagesDir, 0755, true);
            }
            // Remove existing old profile photos
            foreach (glob($imagesDir . '/profile_photo.*') as $oldFile) {
                if (file_exists($oldFile)) @unlink($oldFile);
            }
            $photo->move($imagesDir, 'profile_photo.jpg');
        }


        unset($data['photo']);

        foreach ($data as $sectionKey => $contenu) {
            CvSection::updateOrCreate(
                ['section' => $sectionKey],
                ['contenu' => $contenu]
            );
        }

        return redirect()->back()->with('success', 'Mon CV et ma photo mis à jour avec succès.');
    }

    /**
     * Upload custom PDF document (Anlagen).
     */
    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:15360', // max 15MB
        ], [
            'document.required' => 'Veuillez sélectionner un fichier PDF.',
            'document.mimes'    => 'Le fichier doit être obligatoirement au format PDF.',
            'document.max'      => 'La taille du fichier PDF ne doit pas dépasser 15 Mo.',
        ]);

        $file = $request->file('document');
        $docsDir = storage_path('app/documents');
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

        return response()->download($docPath, 'Anlagen_Yassir_Kezzi.pdf');
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

    /**
     * Download or preview PDF CV.
     */
    public function previewPdf()
    {
        $cvSections = CvSection::all()->keyBy('section');
        $pdf = Pdf::loadView('cv.pdf_template', compact('cvSections'));
        return $pdf->stream('lebenslauf_yassir-kezzi.pdf');
    }
}
