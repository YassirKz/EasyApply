<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>CV - Yassir Kezzi</title>
    <style>
        @page {
            margin: 18mm 16mm 18mm 16mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            font-size: 9.5pt;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563eb;
        }
        h1 {
            font-size: 22pt;
            font-weight: bold;
            color: #1d4ed8;
            margin: 0 0 2px 0;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 11pt;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 6px;
        }
        .contact-info {
            font-size: 8.5pt;
            color: #6b7280;
        }
        .contact-info a {
            color: #2563eb;
            text-decoration: none;
        }
        h2 {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1d4ed8;
            border-bottom: 1.5px solid #2563eb;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .section-content {
            font-size: 9pt;
            color: #374151;
            white-space: pre-line;
            margin-bottom: 8px;
        }
        /* Structured ATS Tables */
        table.ats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }
        table.ats-table td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        table.ats-table td.label-col {
            font-weight: bold;
            color: #1e40af;
            width: 25%;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>

@php
    // Search for any profile photo regardless of extension case
    $photoFiles = array_merge(
        glob(public_path('images/profile_photo.jpg')) ?: [],
        glob(public_path('images/profile_photo.JPG')) ?: [],
        glob(public_path('images/profile_photo.jpeg')) ?: [],
        glob(public_path('images/profile_photo.png')) ?: [],
        glob(public_path('images/profile_photo.PNG')) ?: [],
        glob(public_path('images/profile_photo.webp')) ?: []
    );
    $photoPath = !empty($photoFiles) ? reset($photoFiles) : null;
    $photoBase64 = '';
    if ($photoPath && file_exists($photoPath)) {
        $photoData = file_get_contents($photoPath);
        $extRaw = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
        // DomPDF requires 'jpeg' not 'jpg'
        $mimeExt = ($extRaw === 'jpg') ? 'jpeg' : $extRaw;
        $photoBase64 = 'data:image/' . $mimeExt . ';base64,' . base64_encode($photoData);
    }
@endphp


    <!-- Header Section -->
    <div class="header">
        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
            <tr>
                <td style="border: none; vertical-align: top; padding: 0;">
                    <h1>Yassir Kezzi</h1>
                    <div class="subtitle">Junior Full-Stack Entwickler · Ausbildung Fachinformatiker</div>
                    <div class="contact-info">
                        +212 682 486 661 &nbsp;|&nbsp; kezziyassir005@gmail.com &nbsp;|&nbsp; linkedin.com/in/yassir-kezzi/ &nbsp;|&nbsp; github.com/YassirKz
                    </div>
                </td>
                @if($photoBase64)
                    <td style="border: none; width: 90px; text-align: right; vertical-align: top; padding: 0 0 0 10px;">
                        <img src="{{ $photoBase64 }}" style="width: 85px; height: 105px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e0;" alt="Bewerbungsfoto">
                    </td>
                @endif
            </tr>
        </table>
    </div>


    <!-- 1. BERUFLICHES PROFIL -->
    @if(isset($cvSections['profil']) && !empty($cvSections['profil']->contenu))
        <h2>BERUFLICHES PROFIL</h2>
        <div class="section-content">{{ $cvSections['profil']->contenu }}</div>
    @endif

    <!-- 2. TECHNISCHE KOMPETENZEN -->
    @if(isset($cvSections['competences']) && !empty($cvSections['competences']->contenu))
        <h2>TECHNISCHE KOMPETENZEN</h2>
        <div class="section-content">{{ $cvSections['competences']->contenu }}</div>
    @endif

    <!-- 3. PRAKTIKUM -->
    @if(isset($cvSections['praktikum']) && !empty($cvSections['praktikum']->contenu))
        <h2>PRAKTIKUM</h2>
        <div class="section-content">{{ $cvSections['praktikum']->contenu }}</div>
    @endif

    <!-- 4. PROJEKTERFAHRUNG -->
    @if(isset($cvSections['projekterfahrung']) && !empty($cvSections['projekterfahrung']->contenu))
        <h2>PROJEKTERFAHRUNG</h2>
        <div class="section-content">{{ $cvSections['projekterfahrung']->contenu }}</div>
    @endif

    <!-- 5. AUSBILDUNG -->
    @if(isset($cvSections['ausbildung']) && !empty($cvSections['ausbildung']->contenu))
        <h2>AUSBILDUNG</h2>
        <div class="section-content">{{ $cvSections['ausbildung']->contenu }}</div>
    @endif

    <!-- 6. SPRACHKENNTNISSE -->
    @if(isset($cvSections['langues']) && !empty($cvSections['langues']->contenu))
        <h2>SPRACHKENNTNISSE</h2>
        <div class="section-content">{{ $cvSections['langues']->contenu }}</div>
    @endif

    <!-- 7. PERSÖNLICHE DATEN -->
    @if(isset($cvSections['personliche_daten']) && !empty($cvSections['personliche_daten']->contenu))
        <h2>PERSÖNLICHE DATEN</h2>
        <div class="section-content">{{ $cvSections['personliche_daten']->contenu }}</div>
    @endif

    <!-- 8. INTERESSEN -->
    @if(isset($cvSections['interessen']) && !empty($cvSections['interessen']->contenu))
        <h2>INTERESSEN</h2>
        <div class="section-content">{{ $cvSections['interessen']->contenu }}</div>
    @endif

</body>
</html>
