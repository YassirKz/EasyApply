<?php

namespace Tests\Feature;

use App\Models\CvSection;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests for LettreCvController:
 *   - editLettre  (GET  /lettre)
 *   - updateLettre (POST /lettre)
 *   - editCv      (GET  /cv)
 *   - updateCv    (POST /cv)
 *   - previewPdf  (GET  /cv/pdf)
 */
class LettreCvControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ==================================================================
    // AUTH GUARD
    // ==================================================================

    public function test_lettre_edit_redirects_guest(): void
    {
        $this->get(route('lettre.edit'))->assertRedirect(route('login'));
    }

    public function test_cv_edit_redirects_guest(): void
    {
        $this->get(route('cv.edit'))->assertRedirect(route('login'));
    }

    public function test_cv_pdf_redirects_guest(): void
    {
        $this->get(route('cv.pdf'))->assertRedirect(route('login'));
    }

    // ==================================================================
    // LETTRE – VIEW
    // ==================================================================

    public function test_lettre_edit_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user)
             ->get(route('lettre.edit'))
             ->assertOk()
             ->assertViewIs('lettre_cv.lettre')
             ->assertViewHas('lettre');
    }

    public function test_lettre_edit_shows_empty_string_when_no_template_exists(): void
    {
        $this->actingAs($this->user)
             ->get(route('lettre.edit'))
             ->assertViewHas('lettre', '');
    }

    public function test_lettre_edit_shows_existing_template(): void
    {
        Parametre::create([
            'cle'    => 'modele_lettre',
            'valeur' => 'Sehr geehrte Damen und Herren,',
        ]);

        $this->actingAs($this->user)
             ->get(route('lettre.edit'))
             ->assertViewHas('lettre', 'Sehr geehrte Damen und Herren,');
    }

    // ==================================================================
    // LETTRE – UPDATE
    // ==================================================================

    public function test_update_lettre_creates_parametre_if_missing(): void
    {
        $this->assertDatabaseMissing('parametres', ['cle' => 'modele_lettre']);

        $this->actingAs($this->user)
             ->post(route('lettre.update'), ['valeur' => 'Mein Anschreiben'])
             ->assertRedirect()
             ->assertSessionHas('success');

        $this->assertDatabaseHas('parametres', [
            'cle'    => 'modele_lettre',
            'valeur' => 'Mein Anschreiben',
        ]);
    }

    public function test_update_lettre_updates_existing_parametre(): void
    {
        Parametre::create(['cle' => 'modele_lettre', 'valeur' => 'Alt']);

        $this->actingAs($this->user)
             ->post(route('lettre.update'), ['valeur' => 'Neu'])
             ->assertRedirect();

        $this->assertDatabaseHas('parametres', [
            'cle'    => 'modele_lettre',
            'valeur' => 'Neu',
        ]);
        $this->assertSame(1, Parametre::where('cle', 'modele_lettre')->count());
    }

    public function test_update_lettre_fails_when_valeur_is_empty(): void
    {
        $this->actingAs($this->user)
             ->post(route('lettre.update'), ['valeur' => ''])
             ->assertSessionHasErrors('valeur');
    }

    public function test_update_lettre_requires_valeur_field(): void
    {
        $this->actingAs($this->user)
             ->post(route('lettre.update'), [])
             ->assertSessionHasErrors('valeur');
    }

    // ==================================================================
    // CV – VIEW
    // ==================================================================

    public function test_cv_edit_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user)
             ->get(route('cv.edit'))
             ->assertOk()
             ->assertViewIs('lettre_cv.cv')
             ->assertViewHas('sections');
    }

    public function test_cv_edit_sections_keyed_by_section_name(): void
    {
        CvSection::create(['section' => 'profil', 'contenu' => 'Mein Profil']);

        $response = $this->actingAs($this->user)->get(route('cv.edit'));

        $sections = $response->viewData('sections');
        $this->assertArrayHasKey('profil', $sections->toArray());
    }

    // ==================================================================
    // CV – UPDATE (sections only, no photo)
    // ==================================================================

    public function test_update_cv_creates_sections(): void
    {
        $this->actingAs($this->user)
             ->post(route('cv.update'), [
                 'profil'      => 'Full-Stack Entwickler',
                 'competences' => 'PHP, Laravel, Vue.js',
             ])
             ->assertRedirect()
             ->assertSessionHas('success');

        $this->assertDatabaseHas('cv_sections', [
            'section' => 'profil',
            'contenu' => 'Full-Stack Entwickler',
        ]);
        $this->assertDatabaseHas('cv_sections', [
            'section' => 'competences',
            'contenu' => 'PHP, Laravel, Vue.js',
        ]);
    }

    public function test_update_cv_updates_existing_section(): void
    {
        CvSection::create(['section' => 'profil', 'contenu' => 'Old']);

        $this->actingAs($this->user)
             ->post(route('cv.update'), ['profil' => 'New Profil']);

        $this->assertDatabaseHas('cv_sections', [
            'section' => 'profil',
            'contenu' => 'New Profil',
        ]);
        // Only one row for 'profil'
        $this->assertSame(1, CvSection::where('section', 'profil')->count());
    }

    public function test_update_cv_accepts_all_valid_section_keys(): void
    {
        $sections = [
            'profil'            => 'Profil text',
            'competences'       => 'Competences text',
            'praktikum'         => 'Praktikum text',
            'projekterfahrung'  => 'Projekterfahrung text',
            'ausbildung'        => 'Ausbildung text',
            'langues'           => 'Langues text',
            'personliche_daten' => 'Persönliche Daten',
            'interessen'        => 'Interessen text',
        ];

        $this->actingAs($this->user)
             ->post(route('cv.update'), $sections)
             ->assertRedirect()
             ->assertSessionHas('success');

        foreach ($sections as $section => $contenu) {
            $this->assertDatabaseHas('cv_sections', compact('section', 'contenu'));
        }
    }

    public function test_update_cv_with_photo_stores_file(): void
    {
        // Minimal valid JPEG binary (does not require GD extension)
        $jpegContent = $this->minimalJpeg();
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_photo_' . uniqid() . '.jpg';
        file_put_contents($tmpPath, $jpegContent);

        $photo = new UploadedFile($tmpPath, 'photo.jpg', 'image/jpeg', null, true);

        $this->actingAs($this->user)
             ->post(route('cv.update'), ['photo' => $photo])
             ->assertRedirect()
             ->assertSessionHas('success');

        $this->assertFileExists(public_path('images/profile_photo.jpg'));

        // Cleanup
        @unlink(public_path('images/profile_photo.jpg'));
        @unlink($tmpPath);
    }

    public function test_update_cv_rejects_non_image_photo(): void
    {
        $file = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');

        $this->actingAs($this->user)
             ->post(route('cv.update'), ['photo' => $file])
             ->assertSessionHasErrors('photo');
    }

    public function test_update_cv_rejects_oversized_photo(): void
    {
        // Create a 6 MB JPEG-like file (> 5 MB limit) — no GD needed
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_big_' . uniqid() . '.jpg';
        file_put_contents($tmpPath, str_repeat($this->minimalJpeg(), 1) . str_repeat('X', 6 * 1024 * 1024));

        $file = new UploadedFile($tmpPath, 'big.jpg', 'image/jpeg', null, true);

        $this->actingAs($this->user)
             ->post(route('cv.update'), ['photo' => $file])
             ->assertSessionHasErrors('photo');

        @unlink($tmpPath);
    }

    /**
     * Returns the smallest valid JPEG binary (does not require GD extension).
     * Source: minimal JPEG header + end marker.
     */
    private function minimalJpeg(): string
    {
        return base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8U'
            . 'HRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgN'
            . 'DRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'
            . 'MjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAA'
            . 'AAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/'
            . 'aAAwDAQACEQMRAD8AJQAB/9k='
        );
    }

    // ==================================================================
    // CV PDF PREVIEW
    // ==================================================================

    public function test_pdf_preview_returns_pdf_content_type(): void
    {
        // DomPDF needs at least one CvSection to render
        CvSection::create(['section' => 'profil', 'contenu' => 'Test profil for PDF']);

        $response = $this->actingAs($this->user)->get(route('cv.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
