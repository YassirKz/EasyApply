<?php

namespace Tests\Feature;

use App\Mail\CandidatureMail;
use App\Models\CvSection;
use App\Models\Entreprise;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Feature tests for EnvoiController:
 *   - envoyerMasse  (POST /envoi-masse)
 *   - envoyerTest   (POST /envoi-test)
 *
 * Mail::fake() intercepts emails so no real SMTP connection is made.
 */
class EnvoiControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        // Seed the letter template that CandidatureMail needs
        Parametre::factory()->modelLettre()->create();

        // Seed a minimal CV section so DomPDF doesn't crash on empty data
        CvSection::create(['section' => 'profil', 'contenu' => 'Test Profil']);
    }

    // ==================================================================
    // AUTH GUARD
    // ==================================================================

    public function test_envoi_masse_redirects_guest(): void
    {
        $this->post(route('envoi.masse'))
             ->assertRedirect(route('login'));
    }

    public function test_envoi_test_redirects_guest(): void
    {
        $this->post(route('envoi.test'))
             ->assertRedirect(route('login'));
    }

    // ==================================================================
    // ENVOI MASSE
    // ==================================================================

    public function test_envoi_masse_sends_email_to_each_pending_entreprise(): void
    {
        Mail::fake();

        $pending = Entreprise::factory()->count(3)->create();
        Entreprise::factory()->envoye()->count(2)->create();  // already sent → skip

        $this->actingAs($this->user)
             ->post(route('envoi.masse'))
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        // Only 3 pending companies should receive an email
        Mail::assertSent(CandidatureMail::class, 3);

        foreach ($pending as $e) {
            Mail::assertSent(CandidatureMail::class, fn ($mail) => $mail->hasTo($e->email));
        }
    }

    public function test_envoi_masse_marks_companies_as_sent(): void
    {
        Mail::fake();

        $pending = Entreprise::factory()->count(2)->create();

        $this->actingAs($this->user)->post(route('envoi.masse'));

        foreach ($pending as $e) {
            $this->assertDatabaseHas('entreprises', [
                'id'         => $e->id,
                'est_envoye' => 1,
            ]);
        }
    }

    public function test_envoi_masse_sets_date_envoi_on_success(): void
    {
        Mail::fake();

        $e = Entreprise::factory()->create();

        $this->actingAs($this->user)->post(route('envoi.masse'));

        $fresh = $e->fresh();
        $this->assertNotNull($fresh->date_envoi);
    }

    public function test_envoi_masse_does_not_resend_to_already_sent_companies(): void
    {
        Mail::fake();

        $sentCompany = Entreprise::factory()->envoye()->create();

        $this->actingAs($this->user)->post(route('envoi.masse'));

        Mail::assertNotSent(CandidatureMail::class, fn ($mail) => $mail->hasTo($sentCompany->email));
    }

    public function test_envoi_masse_skips_future_scheduled_companies(): void
    {
        Mail::fake();

        $scheduledCompany = Entreprise::factory()->programme(now()->addDay())->create();
        $immediateCompany = Entreprise::factory()->create();

        $this->actingAs($this->user)->post(route('envoi.masse'));

        // Should only send immediate company, NOT future scheduled company
        Mail::assertSent(CandidatureMail::class, 1);
        Mail::assertSent(CandidatureMail::class, fn ($mail) => $mail->hasTo($immediateCompany->email));
        Mail::assertNotSent(CandidatureMail::class, fn ($mail) => $mail->hasTo($scheduledCompany->email));
    }

    public function test_envoi_masse_with_no_pending_companies_redirects_with_info(): void
    {
        Mail::fake();

        // All companies already sent
        Entreprise::factory()->envoye()->count(2)->create();

        $this->actingAs($this->user)
             ->post(route('envoi.masse'))
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('info');

        Mail::assertNothingSent();
    }

    public function test_envoi_masse_with_empty_db_redirects_with_info(): void
    {
        Mail::fake();

        $this->actingAs($this->user)
             ->post(route('envoi.masse'))
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('info');
    }

    // ==================================================================
    // ENVOI TEST
    // ==================================================================

    public function test_envoi_test_sends_email_to_sender_address(): void
    {
        Mail::fake();

        $e = Entreprise::factory()->create();

        $senderEmail = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'test@example.com'));

        $this->actingAs($this->user)
             ->post(route('envoi.test'))
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        Mail::assertSent(CandidatureMail::class, 1);
        Mail::assertSent(CandidatureMail::class, fn ($mail) => $mail->hasTo($senderEmail));
    }

    public function test_envoi_test_does_not_mark_company_as_sent(): void
    {
        Mail::fake();

        $e = Entreprise::factory()->create();

        $this->actingAs($this->user)->post(route('envoi.test'));

        // est_envoye must still be false
        $this->assertDatabaseHas('entreprises', [
            'id'         => $e->id,
            'est_envoye' => 0,
        ]);
    }

    public function test_envoi_test_prefers_pending_company_over_sent_one(): void
    {
        Mail::fake();

        $sent    = Entreprise::factory()->envoye()->create();
        $pending = Entreprise::factory()->create();

        $this->actingAs($this->user)->post(route('envoi.test'));

        // The success session message should contain the pending company name
        Mail::assertSent(CandidatureMail::class, fn (CandidatureMail $mail) => $mail->entreprise->id === $pending->id);
    }

    public function test_envoi_test_with_no_companies_returns_error(): void
    {
        Mail::fake();

        // Empty DB — no company to use as preview
        $this->actingAs($this->user)
             ->post(route('envoi.test'))
             ->assertRedirect()
             ->assertSessionHas('error');

        Mail::assertNothingSent();
    }

    // ==================================================================
    // CandidatureMail content
    // ==================================================================

    public function test_candidature_mail_envelope_has_correct_subject(): void
    {
        $e = Entreprise::factory()->create();
        $mail = new CandidatureMail($e);

        $envelope = $mail->envelope();
        $this->assertStringContainsString('Bewerbung', $envelope->subject);
        $this->assertStringContainsString('Yassir Kezzi', $envelope->subject);
    }

    public function test_candidature_mail_has_pdf_attachment(): void
    {
        $e = Entreprise::factory()->create();
        $mail = new CandidatureMail($e);

        $attachments = $mail->attachments();
        $this->assertNotEmpty($attachments);

        // Clean up temp PDF
        if (file_exists($mail->pdfPath)) {
            @unlink($mail->pdfPath);
        }
    }

    public function test_candidature_mail_lettre_texte_contains_salutation(): void
    {
        $e = Entreprise::factory()->directeurHomme()->create([
            'texte_personnalise' => 'Ich bewerbe mich.',
        ]);

        $mail = new CandidatureMail($e);

        $this->assertStringContainsString('Sehr geehrter Herr', $mail->lettreTexte);

        if (file_exists($mail->pdfPath)) {
            @unlink($mail->pdfPath);
        }
    }

    public function test_candidature_mail_replaces_texte_personnalise_placeholder(): void
    {
        $e = Entreprise::factory()->create([
            'directeur'         => 'Frau Wagner',
            'texte_personnalise' => 'Ich bin sehr motiviert.',
        ]);

        $mail = new CandidatureMail($e);
        $this->assertStringContainsString('Ich bin sehr motiviert', $mail->lettreTexte);

        if (file_exists($mail->pdfPath)) {
            @unlink($mail->pdfPath);
        }
    }

    public function test_candidature_mail_attaches_custom_documents_pdf_if_present(): void
    {
        $docsDir = storage_path('app/documents');
        if (!file_exists($docsDir)) mkdir($docsDir, 0755, true);
        file_put_contents($docsDir . '/anlagen.pdf', '%PDF-1.4 Custom Documents Content');

        $e = Entreprise::factory()->create();
        $mail = new CandidatureMail($e);

        $attachments = $mail->attachments();
        $this->assertCount(2, $attachments);

        // Cleanup
        @unlink($docsDir . '/anlagen.pdf');
        if (file_exists($mail->pdfPath)) {
            @unlink($mail->pdfPath);
        }
    }
}
