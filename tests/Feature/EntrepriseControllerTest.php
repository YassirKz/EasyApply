<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for EntrepriseController (CRUD + search + filters + JSON + AI + import).
 *
 * Every test authenticates as a real User (auth middleware required).
 * The in-memory SQLite DB is refreshed between tests.
 */
class EntrepriseControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ==================================================================
    // INDEX
    // ==================================================================

    public function test_index_redirects_guest_to_login(): void
    {
        $response = $this->get(route('entreprises.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user)
             ->get(route('entreprises.index'))
             ->assertOk()
             ->assertViewIs('entreprises.index')
             ->assertViewHasAll(['entreprises', 'pendingCount', 'sentCount']);
    }

    public function test_index_shows_list_of_entreprises(): void
    {
        Entreprise::factory()->count(3)->create();

        $this->actingAs($this->user)
             ->get(route('entreprises.index'))
             ->assertOk()
             ->assertViewHas('entreprises', fn ($p) => $p->total() === 3);
    }

    public function test_index_search_filters_by_nom(): void
    {
        Entreprise::factory()->create(['nom' => 'Bosch GmbH']);
        Entreprise::factory()->create(['nom' => 'Allianz AG']);

        $response = $this->actingAs($this->user)
                         ->get(route('entreprises.index', ['search' => 'Bosch']));

        $response->assertOk()
                 ->assertViewHas('entreprises', fn ($p) => $p->total() === 1);
    }

    public function test_index_search_filters_by_email(): void
    {
        Entreprise::factory()->create(['email' => 'hr@bosch.de']);
        Entreprise::factory()->create(['email' => 'info@allianz.de']);

        $response = $this->actingAs($this->user)
                         ->get(route('entreprises.index', ['search' => 'bosch']));

        $response->assertOk()
                 ->assertViewHas('entreprises', fn ($p) => $p->total() === 1);
    }

    public function test_index_search_filters_by_directeur(): void
    {
        Entreprise::factory()->create(['directeur' => 'Herr Müller']);
        Entreprise::factory()->create(['directeur' => 'Frau Schmidt']);

        $response = $this->actingAs($this->user)
                         ->get(route('entreprises.index', ['search' => 'Müller']));

        $response->assertOk()
                 ->assertViewHas('entreprises', fn ($p) => $p->total() === 1);
    }

    public function test_index_statut_filter_attente(): void
    {
        Entreprise::factory()->count(2)->create();
        Entreprise::factory()->envoye()->count(3)->create();

        $response = $this->actingAs($this->user)
                         ->get(route('entreprises.index', ['statut' => 'attente']));

        $response->assertOk()
                 ->assertViewHas('entreprises', fn ($p) => $p->total() === 2);
    }

    public function test_index_statut_filter_envoye(): void
    {
        Entreprise::factory()->count(2)->create();
        Entreprise::factory()->envoye()->count(3)->create();

        $response = $this->actingAs($this->user)
                         ->get(route('entreprises.index', ['statut' => 'envoye']));

        $response->assertOk()
                 ->assertViewHas('entreprises', fn ($p) => $p->total() === 3);
    }

    public function test_index_statut_filter_relance(): void
    {
        // 1 company sent 20 days ago (due for relance)
        Entreprise::factory()->create([
            'est_envoye' => true,
            'date_envoi' => now()->subDays(20),
        ]);

        // 1 company sent 5 days ago (NOT due for relance)
        Entreprise::factory()->create([
            'est_envoye' => true,
            'date_envoi' => now()->subDays(5),
        ]);

        // 1 pending company
        Entreprise::factory()->create(['est_envoye' => false]);

        $response = $this->actingAs($this->user)
                         ->get(route('entreprises.index', ['statut' => 'relance']));

        $response->assertOk()
                 ->assertViewHas('entreprises', fn ($p) => $p->total() === 1);
    }

    public function test_index_pending_sent_and_relance_counts_are_correct(): void
    {
        Entreprise::factory()->count(4)->create();
        Entreprise::factory()->create([
            'est_envoye' => true,
            'date_envoi' => now()->subDays(16),
        ]);

        $this->actingAs($this->user)
             ->get(route('entreprises.index'))
             ->assertViewHas('pendingCount', 4)
             ->assertViewHas('sentCount', 1)
             ->assertViewHas('relanceCount', 1);
    }

    // ==================================================================
    // STORE
    // ==================================================================

    public function test_store_creates_entreprise_and_redirects(): void
    {
        $this->actingAs($this->user)
             ->post(route('entreprises.store'), [
                 'nom'       => 'Test GmbH',
                 'email'     => 'contact@test.de',
                 'directeur' => 'Herr Fischer',
             ])
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('entreprises', [
            'nom'   => 'Test GmbH',
            'email' => 'contact@test.de',
        ]);
    }

    public function test_store_strips_html_from_nom(): void
    {
        $this->actingAs($this->user)
             ->post(route('entreprises.store'), [
                 'nom'       => '<script>alert(1)</script>Evil GmbH',
                 'email'     => 'evil@test.de',
                 'directeur' => 'Herr Test',
             ]);

        $e = Entreprise::where('email', 'evil@test.de')->first();
        $this->assertNotNull($e);
        $this->assertStringNotContainsString('<script>', $e->nom);
    }

    public function test_store_fails_without_required_nom(): void
    {
        $this->actingAs($this->user)
             ->post(route('entreprises.store'), [
                 'email'     => 'noname@test.de',
                 'directeur' => 'Herr Test',
             ])
             ->assertSessionHasErrors('nom');
    }

    public function test_store_fails_without_required_email(): void
    {
        $this->actingAs($this->user)
             ->post(route('entreprises.store'), [
                 'nom'       => 'Missing Email GmbH',
                 'directeur' => 'Herr Test',
             ])
             ->assertSessionHasErrors('email');
    }

    public function test_store_fails_with_invalid_email_format(): void
    {
        $this->actingAs($this->user)
             ->post(route('entreprises.store'), [
                 'nom'       => 'Bad Mail GmbH',
                 'email'     => 'not-an-email',
                 'directeur' => 'Herr Test',
             ])
             ->assertSessionHasErrors('email');
    }

    public function test_store_fails_with_duplicate_email(): void
    {
        Entreprise::factory()->create(['email' => 'dup@test.de']);

        $this->actingAs($this->user)
             ->post(route('entreprises.store'), [
                 'nom'       => 'Dup GmbH',
                 'email'     => 'dup@test.de',
                 'directeur' => 'Herr Test',
             ])
             ->assertSessionHasErrors('email');
    }

    public function test_store_allows_optional_fields_to_be_empty(): void
    {
        $this->actingAs($this->user)
             ->post(route('entreprises.store'), [
                 'nom'       => 'Minimal GmbH',
                 'email'     => 'min@test.de',
                 'directeur' => 'Herr Min',
             ])
             ->assertRedirect(route('entreprises.index'));

        $this->assertDatabaseHas('entreprises', ['email' => 'min@test.de']);
    }

    // ==================================================================
    // UPDATE
    // ==================================================================

    public function test_update_modifies_entreprise_and_redirects(): void
    {
        $e = Entreprise::factory()->create();

        $this->actingAs($this->user)
             ->put(route('entreprises.update', $e), [
                 'nom'       => 'Updated GmbH',
                 'email'     => $e->email,
                 'directeur' => 'Frau Neumann',
             ])
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('entreprises', [
            'id'        => $e->id,
            'nom'       => 'Updated GmbH',
            'directeur' => 'Frau Neumann',
        ]);
    }

    public function test_update_allows_same_email_for_the_same_entreprise(): void
    {
        $e = Entreprise::factory()->create(['email' => 'same@test.de']);

        $this->actingAs($this->user)
             ->put(route('entreprises.update', $e), [
                 'nom'       => $e->nom,
                 'email'     => 'same@test.de',   // same email → allowed
                 'directeur' => $e->directeur,
             ])
             ->assertRedirect(route('entreprises.index'));
    }

    public function test_update_fails_with_email_of_another_entreprise(): void
    {
        $e1 = Entreprise::factory()->create(['email' => 'taken@test.de']);
        $e2 = Entreprise::factory()->create();

        $this->actingAs($this->user)
             ->put(route('entreprises.update', $e2), [
                 'nom'       => $e2->nom,
                 'email'     => 'taken@test.de',   // belongs to e1 → conflict
                 'directeur' => $e2->directeur,
             ])
             ->assertSessionHasErrors('email');
    }

    // ==================================================================
    // DESTROY (single)
    // ==================================================================

    public function test_destroy_deletes_entreprise_and_redirects(): void
    {
        $e = Entreprise::factory()->create();

        $this->actingAs($this->user)
             ->delete(route('entreprises.destroy', $e))
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseMissing('entreprises', ['id' => $e->id]);
    }

    // ==================================================================
    // DESTROY BATCH
    // ==================================================================

    public function test_destroy_batch_deletes_selected_entreprises(): void
    {
        $toDelete = Entreprise::factory()->count(2)->create();
        $toKeep   = Entreprise::factory()->create();

        $this->actingAs($this->user)
             ->delete(route('entreprises.destroyBatch'), [
                 'ids' => $toDelete->pluck('id')->toArray(),
             ])
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        foreach ($toDelete as $e) {
            $this->assertDatabaseMissing('entreprises', ['id' => $e->id]);
        }
        $this->assertDatabaseHas('entreprises', ['id' => $toKeep->id]);
    }

    public function test_destroy_batch_requires_valid_ids(): void
    {
        $this->actingAs($this->user)
             ->delete(route('entreprises.destroyBatch'), ['ids' => [99999]])
             ->assertSessionHasErrors('ids.0');
    }

    // ==================================================================
    // DESTROY ALL
    // ==================================================================

    public function test_destroy_all_removes_every_entreprise(): void
    {
        Entreprise::factory()->count(5)->create();

        $this->actingAs($this->user)
             ->delete(route('entreprises.destroyAll'))
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        $this->assertSame(0, Entreprise::count());
    }

    // ==================================================================
    // SHOW JSON
    // ==================================================================

    public function test_show_json_returns_correct_structure(): void
    {
        $e = Entreprise::factory()->create([
            'nom'       => 'JSON Test GmbH',
            'email'     => 'json@test.de',
            'directeur' => 'Herr JSON',
        ]);

        $this->actingAs($this->user)
             ->getJson(route('entreprises.showJson', $e))
             ->assertOk()
             ->assertJsonFragment([
                 'id'        => $e->id,
                 'nom'       => 'JSON Test GmbH',
                 'email'     => 'json@test.de',
                 'directeur' => 'Herr JSON',
             ])
             ->assertJsonStructure([
                 'id', 'nom', 'email', 'directeur',
                 'telephone', 'secteur', 'texte_personnalise', 'est_envoye',
             ]);
    }

    public function test_show_json_decodes_html_entities(): void
    {
        $e = Entreprise::factory()->create([
            'nom' => 'M&#252;ller &amp; S&#246;hne GmbH',   // stored encoded
        ]);

        $response = $this->actingAs($this->user)
                         ->getJson(route('entreprises.showJson', $e));

        // The JSON endpoint must return decoded characters
        $nom = $response->json('nom');
        $this->assertStringNotContainsString('&amp;', $nom);
    }

    // ==================================================================
    // GENERATE AI (single)
    // ==================================================================

    public function test_generate_ai_returns_json_with_texte_personnalise(): void
    {
        // Force fallback (no real API key in test environment)
        config(['services.gemini.key' => '']);

        $e = Entreprise::factory()->create(['secteur' => 'IT']);

        $response = $this->actingAs($this->user)
                         ->postJson(route('entreprises.gemini', $e));

        $response->assertOk()
                 ->assertJsonStructure(['success', 'texte_personnalise'])
                 ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('entreprises', [
            'id'                 => $e->id,
            'texte_personnalise' => $response->json('texte_personnalise'),
        ]);
    }

    public function test_generate_ai_updates_db_record(): void
    {
        config(['services.gemini.key' => '']);

        $e = Entreprise::factory()->create(['texte_personnalise' => null]);

        $this->actingAs($this->user)
             ->postJson(route('entreprises.gemini', $e));

        $e->refresh();
        $this->assertNotNull($e->texte_personnalise);
    }

    // ==================================================================
    // CSV / EXCEL IMPORT
    // ==================================================================

    public function test_import_adds_new_entreprises_from_csv(): void
    {
        $csv = "nom,email,directeur,telephone,secteur\n"
             . "Bosch GmbH,hr@bosch.de,Herr Bosch,+49111,IT\n"
             . "Allianz AG,contact@allianz.de,Frau Allianz,+49222,Finanzen\n";

        $file = $this->createTempCsv($csv);

        $this->actingAs($this->user)
             ->post(route('entreprises.import'), ['file' => $file])
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        $this->assertDatabaseHas('entreprises', ['email' => 'hr@bosch.de']);
        $this->assertDatabaseHas('entreprises', ['email' => 'contact@allianz.de']);
    }

    public function test_import_skips_rows_without_valid_email(): void
    {
        $csv = "nom,email,directeur\n"
             . "Valid GmbH,valid@valid.de,Herr Valid\n"
             . "NoEmail GmbH,not-an-email,Herr NoEmail\n";

        $file = $this->createTempCsv($csv);

        $this->actingAs($this->user)
             ->post(route('entreprises.import'), ['file' => $file]);

        $this->assertDatabaseHas('entreprises', ['email' => 'valid@valid.de']);
        $this->assertDatabaseMissing('entreprises', ['nom' => 'NoEmail GmbH']);
    }

    public function test_import_updates_existing_entreprise_on_same_email(): void
    {
        Entreprise::factory()->create([
            'email' => 'existing@test.de',
            'nom'   => 'Old Name GmbH',
        ]);

        $csv = "nom,email,directeur\n"
             . "New Name GmbH,existing@test.de,Herr New\n";

        $file = $this->createTempCsv($csv);

        $this->actingAs($this->user)
             ->post(route('entreprises.import'), ['file' => $file]);

        $this->assertDatabaseHas('entreprises', [
            'email' => 'existing@test.de',
            'nom'   => 'New Name GmbH',
        ]);
    }

    public function test_import_detects_semicolon_delimiter(): void
    {
        $csv = "nom;email;directeur\n"
             . "Semicolon GmbH;semi@test.de;Herr Semi\n";

        $file = $this->createTempCsv($csv);

        $this->actingAs($this->user)
             ->post(route('entreprises.import'), ['file' => $file]);

        $this->assertDatabaseHas('entreprises', ['email' => 'semi@test.de']);
    }

    public function test_import_rejects_invalid_file_type(): void
    {
        // Create a fake text file with .php extension (not allowed)
        $file = \Illuminate\Http\UploadedFile::fake()->create('malware.php', 10, 'application/x-php');

        $this->actingAs($this->user)
             ->post(route('entreprises.import'), ['file' => $file])
             ->assertSessionHasErrors('file');
    }

    public function test_import_requires_a_file(): void
    {
        $this->actingAs($this->user)
             ->post(route('entreprises.import'), [])
             ->assertSessionHasErrors('file');
    }

    // ------------------------------------------------------------------
    // Helper: create a real UploadedFile from a CSV string
    // ------------------------------------------------------------------

    private function createTempCsv(string $content): \Illuminate\Http\UploadedFile
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'test_import_') . '.csv';
        file_put_contents($tmpPath, $content);

        return new \Illuminate\Http\UploadedFile(
            $tmpPath,
            'import.csv',
            'text/csv',
            null,
            true  // test mode: skip is_uploaded_file() check
        );
    }

    // ==================================================================
    // EXTRACT FROM TEXT (AI EXTRACTION)
    // ==================================================================

    public function test_extract_from_text_redirects_guest(): void
    {
        $this->postJson(route('entreprises.extractIa'), ['texte_offre' => str_repeat('a', 30)])
             ->assertUnauthorized();
    }

    public function test_extract_from_text_requires_texte_offre(): void
    {
        $this->actingAs($this->user)
             ->postJson(route('entreprises.extractIa'), [])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['texte_offre']);
    }

    public function test_extract_from_text_rejects_short_text(): void
    {
        $this->actingAs($this->user)
             ->postJson(route('entreprises.extractIa'), ['texte_offre' => 'Kurz'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['texte_offre']);
    }

    public function test_extract_from_text_returns_json_with_expected_keys(): void
    {
        // Uses fallback extractor (no real API key in test environment).
        // A job offer text with email to trigger the email extractor.
        $offerText = 'Wir suchen einen Entwickler bei BMW GmbH. '
            . 'Bitte senden Sie Ihre Bewerbung an: bewerbung@bmw.de. '
            . 'Ansprechpartnerin ist Frau Müller. Branche: Automotive.';

        $response = $this->actingAs($this->user)
                         ->postJson(route('entreprises.extractIa'), ['texte_offre' => $offerText]);

        $response->assertOk()
                 ->assertJsonStructure(['success', 'firma', 'email', 'direktor', 'secteur']);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['firma']);
        $this->assertNotEmpty($data['email']);
    }

    public function test_extract_from_text_sanitizes_output(): void
    {
        // The response values must not contain raw HTML tags (XSS protection).
        $maliciousText = str_repeat('<script>alert(1)</script> BMW GmbH contact@bmw.de Automotive', 5);

        $response = $this->actingAs($this->user)
                         ->postJson(route('entreprises.extractIa'), ['texte_offre' => $maliciousText]);

        $response->assertOk();
        $data = $response->json();

        $this->assertStringNotContainsString('<script>', $data['firma'] ?? '');
        $this->assertStringNotContainsString('<script>', $data['email'] ?? '');
        $this->assertStringNotContainsString('<script>', $data['direktor'] ?? '');
        $this->assertStringNotContainsString('<script>', $data['secteur'] ?? '');
    }

    // ==================================================================
    // SEARCH (now: EXTRACT FROM TEXT) — only accepts job offer text
    // ==================================================================

    public function test_search_via_extract_redirects_guest(): void
    {
        $this->postJson(route('entreprises.extractIa'), ['texte_offre' => str_repeat('a', 30)])
             ->assertUnauthorized();
    }

    public function test_search_via_extract_requires_texte_offre(): void
    {
        $this->actingAs($this->user)
             ->postJson(route('entreprises.extractIa'), [])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['texte_offre']);
    }

    public function test_search_via_extract_rejects_short_text(): void
    {
        $this->actingAs($this->user)
             ->postJson(route('entreprises.extractIa'), ['texte_offre' => 'a'])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['texte_offre']);
    }

    public function test_search_via_extract_handles_job_offer_containing_company_name(): void
    {
        $offerText = 'Stellenanzeige: Wir suchen Entwickler bei Siemens AG. Bewerbungen an karriere@siemens.de. Ansprechpartner: Herr Schmidt. Branche: Industrie.';

        $response = $this->actingAs($this->user)
                         ->postJson(route('entreprises.extractIa'), ['texte_offre' => $offerText]);

        $response->assertOk()
                 ->assertJsonStructure(['success', 'firma', 'email', 'direktor', 'secteur']);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['firma']);
        $this->assertNotEmpty($data['email']);
    }

    public function test_search_via_extract_handles_job_offer_containing_url(): void
    {
        $offerText = 'Offerte: Mehr Infos auf https://www.bosch.de — bitte bewerben an kontakt@bosch.de. Ansprechpartner: Frau Becker. Branche: Automotive.';

        $response = $this->actingAs($this->user)
                         ->postJson(route('entreprises.extractIa'), ['texte_offre' => $offerText]);

        $response->assertOk()
                 ->assertJsonStructure(['success', 'firma', 'email', 'direktor', 'secteur']);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertStringContainsString('Bosch', $data['firma'] ?? '');
        $this->assertStringContainsString('bosch', $data['email'] ?? '');
    }

    public function test_search_via_extract_sanitizes_output(): void
    {
        $offerText = '<script>alert("xss")</script> Wir suchen bei SAP GmbH. Bewerbungen an hr@sap.de';

        $response = $this->actingAs($this->user)
                         ->postJson(route('entreprises.extractIa'), ['texte_offre' => $offerText]);

        $response->assertOk();
        $data = $response->json();

        $this->assertStringNotContainsString('<script>', $data['firma'] ?? '');
        $this->assertStringNotContainsString('<script>', $data['email'] ?? '');
        $this->assertStringNotContainsString('<script>', $data['direktor'] ?? '');
        $this->assertStringNotContainsString('<script>', $data['secteur'] ?? '');
    }
}

