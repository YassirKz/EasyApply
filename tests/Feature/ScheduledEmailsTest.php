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

class ScheduledEmailsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        Parametre::factory()->modelLettre()->create();
        CvSection::create(['section' => 'profil', 'contenu' => 'Test']);
    }

    public function test_user_can_schedule_mass_emails(): void
    {
        Entreprise::factory()->count(3)->create();

        $futureDate = now()->addDay()->format('Y-m-d\TH:i');

        $this->actingAs($this->user)
             ->post(route('envoi.programmer'), [
                 'programmation_envoi' => $futureDate,
             ])
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        foreach (Entreprise::all() as $e) {
            $this->assertNotNull($e->programmation_envoi);
        }
    }

    public function test_scheduling_fails_if_date_in_past(): void
    {
        Entreprise::factory()->create();

        $pastDate = now()->subDay()->format('Y-m-d\TH:i');

        $this->actingAs($this->user)
             ->post(route('envoi.programmer'), [
                 'programmation_envoi' => $pastDate,
             ])
             ->assertSessionHasErrors('programmation_envoi');
    }

    public function test_user_can_cancel_scheduled_email(): void
    {
        $e = Entreprise::factory()->programme()->create();

        $this->actingAs($this->user)
             ->delete(route('envoi.annulerProgrammation', $e))
             ->assertRedirect(route('entreprises.index'))
             ->assertSessionHas('success');

        $this->assertNull($e->fresh()->programmation_envoi);
    }

    public function test_artisan_command_sends_due_scheduled_emails(): void
    {
        Mail::fake();

        // 2 due (scheduled in past/now)
        $due1 = Entreprise::factory()->programme(now()->subMinute())->create();
        $due2 = Entreprise::factory()->programme(now()->subHour())->create();

        // 1 future scheduled (must NOT be sent yet)
        $future = Entreprise::factory()->programme(now()->addHour())->create();

        // 1 already sent (must NOT be sent)
        $sent = Entreprise::factory()->envoye()->create();

        // Execute Artisan command
        $this->artisan('app:send-scheduled')
             ->assertSuccessful();

        Mail::assertSent(CandidatureMail::class, 2);
        Mail::assertSent(CandidatureMail::class, fn ($mail) => $mail->hasTo($due1->email));
        Mail::assertSent(CandidatureMail::class, fn ($mail) => $mail->hasTo($due2->email));
        Mail::assertNotSent(CandidatureMail::class, fn ($mail) => $mail->hasTo($future->email));

        $this->assertTrue($due1->fresh()->est_envoye);
        $this->assertNull($due1->fresh()->programmation_envoi);
        $this->assertFalse($future->fresh()->est_envoye);
    }
}
