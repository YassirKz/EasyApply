<?php

namespace Tests\Unit;

use App\Mail\CandidatureMail;
use Tests\TestCase;

/**
 * Unit tests for CandidatureMail::buildSalutation()
 *
 * Covers every detection branch:
 *  - Empty / generic / HR names → neutral formula
 *  - Female prefixes (Frau, Mrs, Ms, Madame, Mme)
 *  - Male prefixes (Herr, Mr, M., Monsieur)
 *  - Unprefixed names → fallback formula
 */
class CandidatureSalutationTest extends TestCase
{
    // ------------------------------------------------------------------
    // NEUTRAL CASES (empty or generic roles)
    // ------------------------------------------------------------------

    public function test_empty_name_returns_neutral(): void
    {
        $this->assertSame(
            'Sehr geehrte Damen und Herren',
            CandidatureMail::buildSalutation('')
        );
    }

    public function test_whitespace_only_returns_neutral(): void
    {
        $this->assertSame(
            'Sehr geehrte Damen und Herren',
            CandidatureMail::buildSalutation('   ')
        );
    }

    /** @dataProvider genericRolesProvider */
    public function test_generic_role_returns_neutral(string $role): void
    {
        $this->assertSame(
            'Sehr geehrte Damen und Herren',
            CandidatureMail::buildSalutation($role)
        );
    }

    public static function genericRolesProvider(): array
    {
        return [
            ['Responsable Recrutement'],
            ['Ausbildungsleitung'],
            ['Personalabteilung'],
            ['HR'],
            ['Recruiter'],
            ['Team'],
            ['Non Spécifié'],
            // Case-insensitive
            ['hr'],
            ['TEAM'],
        ];
    }

    // ------------------------------------------------------------------
    // FEMALE DETECTION
    // ------------------------------------------------------------------

    public function test_frau_prefix_produces_sehr_geehrte_frau(): void
    {
        $result = CandidatureMail::buildSalutation('Frau Müller');
        $this->assertStringStartsWith('Sehr geehrte Frau', $result);
        $this->assertStringContainsString('Müller', $result);
    }

    public function test_mrs_prefix_produces_sehr_geehrte_frau(): void
    {
        $result = CandidatureMail::buildSalutation('Mrs Smith');
        $this->assertStringStartsWith('Sehr geehrte Frau', $result);
        $this->assertStringContainsString('Smith', $result);
    }

    public function test_ms_prefix_produces_sehr_geehrte_frau(): void
    {
        $result = CandidatureMail::buildSalutation('Ms Johnson');
        $this->assertStringStartsWith('Sehr geehrte Frau', $result);
    }

    public function test_madame_prefix_produces_sehr_geehrte_frau(): void
    {
        $result = CandidatureMail::buildSalutation('Madame Dupont');
        $this->assertStringStartsWith('Sehr geehrte Frau', $result);
        $this->assertStringContainsString('Dupont', $result);
    }

    public function test_mme_prefix_produces_sehr_geehrte_frau(): void
    {
        $result = CandidatureMail::buildSalutation('Mme Leclerc');
        $this->assertStringStartsWith('Sehr geehrte Frau', $result);
        $this->assertStringContainsString('Leclerc', $result);
    }

    public function test_female_detection_is_case_insensitive(): void
    {
        $result = CandidatureMail::buildSalutation('FRAU Schmidt');
        $this->assertStringStartsWith('Sehr geehrte Frau', $result);
    }

    // ------------------------------------------------------------------
    // MALE DETECTION
    // ------------------------------------------------------------------

    public function test_herr_prefix_produces_sehr_geehrter_herr(): void
    {
        $result = CandidatureMail::buildSalutation('Herr Weber');
        $this->assertStringStartsWith('Sehr geehrter Herr', $result);
        $this->assertStringContainsString('Weber', $result);
    }

    public function test_mr_prefix_produces_sehr_geehrter_herr(): void
    {
        $result = CandidatureMail::buildSalutation('Mr Anderson');
        $this->assertStringStartsWith('Sehr geehrter Herr', $result);
        $this->assertStringContainsString('Anderson', $result);
    }

    public function test_monsieur_prefix_produces_sehr_geehrter_herr(): void
    {
        $result = CandidatureMail::buildSalutation('Monsieur Martin');
        $this->assertStringStartsWith('Sehr geehrter Herr', $result);
        $this->assertStringContainsString('Martin', $result);
    }

    public function test_m_dot_prefix_produces_sehr_geehrter_herr(): void
    {
        $result = CandidatureMail::buildSalutation('M. Lefebvre');
        $this->assertStringStartsWith('Sehr geehrter Herr', $result);
    }

    public function test_male_detection_is_case_insensitive(): void
    {
        $result = CandidatureMail::buildSalutation('HERR Braun');
        $this->assertStringStartsWith('Sehr geehrter Herr', $result);
    }

    // ------------------------------------------------------------------
    // UNPREFIXED NAMES → generic fallback
    // ------------------------------------------------------------------

    public function test_unprefixed_name_returns_generic_fallback(): void
    {
        $result = CandidatureMail::buildSalutation('Thomas Klein');
        $this->assertStringContainsString('Frau/Herr', $result);
        $this->assertStringContainsString('Thomas Klein', $result);
    }

    public function test_single_word_name_returns_generic_fallback(): void
    {
        $result = CandidatureMail::buildSalutation('Schneider');
        $this->assertStringContainsString('Frau/Herr', $result);
        $this->assertStringContainsString('Schneider', $result);
    }

    // ------------------------------------------------------------------
    // HTML stripping
    // ------------------------------------------------------------------

    public function test_html_tags_are_stripped_before_processing(): void
    {
        $result = CandidatureMail::buildSalutation('<b>Frau</b> <em>Wagner</em>');
        $this->assertStringStartsWith('Sehr geehrte Frau', $result);
        $this->assertStringNotContainsString('<b>', $result);
    }
}
