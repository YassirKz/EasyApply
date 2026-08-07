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

    /**
     * View and edit CV sections.
     */
    public function editCv()
    {
        $sections = CvSection::all()->keyBy('section');
        return view('lettre_cv.cv', compact('sections'));
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
     * Download or preview PDF CV.
     */
    public function previewPdf()
    {
        $cvSections = CvSection::all()->keyBy('section');
        $pdf = Pdf::loadView('cv.pdf_template', compact('cvSections'));
        return $pdf->stream('CV_Yassir_Kezzi_Preview.pdf');
    }
}
