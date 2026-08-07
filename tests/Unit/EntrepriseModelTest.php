<?php

namespace Tests\Unit;

use App\Models\Entreprise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the Entreprise Eloquent model.
 *
 * Covers: fillable fields, type casts, factory states,
 * and scope-equivalent query behaviour.
 */
class EntrepriseModelTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Schema / Model basics
    // ------------------------------------------------------------------

    public function test_can_create_entreprise_via_factory(): void
    {
        $e = Entreprise::factory()->create();

        $this->assertNotNull($e->id);
        $this->assertNotEmpty($e->nom);
        $this->assertNotEmpty($e->email);
    }

    public function test_est_envoye_defaults_to_false(): void
    {
        $e = Entreprise::factory()->create();
        $this->assertFalse($e->est_envoye);
    }

    public function test_date_envoi_defaults_to_null(): void
    {
        $e = Entreprise::factory()->create();
        $this->assertNull($e->date_envoi);
    }

    public function test_est_envoye_is_cast_to_boolean(): void
    {
        $e = Entreprise::factory()->envoye()->create();

        // Re-fetch from DB to ensure cast is applied
        $fresh = Entreprise::find($e->id);
        $this->assertIsBool($fresh->est_envoye);
        $this->assertTrue($fresh->est_envoye);
    }

    public function test_date_envoi_is_cast_to_carbon_when_set(): void
    {
        $e = Entreprise::factory()->envoye()->create();
        $fresh = Entreprise::find($e->id);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fresh->date_envoi);
    }

    // ------------------------------------------------------------------
    // Factory states
    // ------------------------------------------------------------------

    public function test_factory_envoye_state_sets_est_envoye_true(): void
    {
        $e = Entreprise::factory()->envoye()->create();
        $this->assertTrue($e->est_envoye);
        $this->assertNotNull($e->date_envoi);
    }

    public function test_factory_avec_texte_state_sets_texte_personnalise(): void
    {
        $e = Entreprise::factory()->avecTexte()->create();
        $this->assertNotNull($e->texte_personnalise);
    }

    public function test_factory_directrice_femme_state_contains_frau(): void
    {
        $e = Entreprise::factory()->directriceFemme()->create();
        $this->assertStringContainsStringIgnoringCase('Frau', $e->directeur);
    }

    public function test_factory_directeur_homme_state_contains_herr(): void
    {
        $e = Entreprise::factory()->directeurHomme()->create();
        $this->assertStringContainsStringIgnoringCase('Herr', $e->directeur);
    }

    // ------------------------------------------------------------------
    // Fillable / mass-assignment
    // ------------------------------------------------------------------

    public function test_fillable_fields_can_be_mass_assigned(): void
    {
        $e = Entreprise::create([
            'nom'               => 'Test GmbH',
            'email'             => 'test@test.de',
            'directeur'         => 'Herr Test',
            'telephone'         => '+49 123 456',
            'secteur'           => 'IT',
            'texte_personnalise' => 'Ein Text.',
        ]);

        $this->assertDatabaseHas('entreprises', [
            'nom'   => 'Test GmbH',
            'email' => 'test@test.de',
        ]);
    }

    // ------------------------------------------------------------------
    // DB queries / counting
    // ------------------------------------------------------------------

    public function test_pending_count_only_counts_est_envoye_false(): void
    {
        Entreprise::factory()->count(3)->create();             // pending
        Entreprise::factory()->envoye()->count(2)->create();   // sent

        $pending = Entreprise::where('est_envoye', false)->count();
        $sent    = Entreprise::where('est_envoye', true)->count();

        $this->assertSame(3, $pending);
        $this->assertSame(2, $sent);
    }

    public function test_unique_email_constraint_at_application_level(): void
    {
        Entreprise::factory()->create(['email' => 'unique@test.de']);

        // Second create with same email must fail DB unique constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        Entreprise::factory()->create(['email' => 'unique@test.de']);
    }
}
