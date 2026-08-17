<?php

namespace Tests\Unit;

use App\Services\GeminiService;
use Tests\TestCase;

/**
 * Unit tests for GeminiService::generatePersonalizedText()
 *
 * We test only the **fallback path** (no real API key) which is
 * deterministic and safe to run offline without mocking HTTP.
 */
class GeminiServiceTest extends TestCase
{
    private GeminiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Force empty API key → always use fallback patterns
        config(['services.gemini.key' => '']);
        $this->service = new GeminiService();
    }

    // ------------------------------------------------------------------
    // Fallback text generation
    // ------------------------------------------------------------------

    public function test_returns_non_empty_string(): void
    {
        $text = $this->service->generatePersonalizedText('TechCorp GmbH');
        $this->assertNotEmpty($text);
        $this->assertIsString($text);
    }

    public function test_company_name_is_included_in_text(): void
    {
        $text = $this->service->generatePersonalizedText('Müller AG');
        $this->assertStringContainsString('Müller AG', $text);
    }

    public function test_sector_is_included_when_provided(): void
    {
        $text = $this->service->generatePersonalizedText('Siemens GmbH', 'IT');
        $this->assertStringContainsString('IT', $text);
    }

    public function test_different_companies_can_get_different_texts(): void
    {
        // Because the pattern index depends on crc32(name), two different
        // names with a large enough crc32 difference should resolve differently.
        $texts = [];
        $companies = ['Alpha GmbH', 'Beta AG', 'Gamma Ltd', 'Delta KG', 'Epsilon GmbH'];

        foreach ($companies as $company) {
            $texts[] = $this->service->generatePersonalizedText($company);
        }

        // At least 2 distinct texts among the 5 companies
        $uniqueTexts = array_unique($texts);
        $this->assertGreaterThanOrEqual(2, count($uniqueTexts));
    }

    public function test_same_company_always_returns_same_text(): void
    {
        $service = new GeminiService();

        $text1 = $service->generatePersonalizedText('Bosch GmbH', 'Technologie');
        $text2 = $service->generatePersonalizedText('Bosch GmbH', 'Technologie');

        $this->assertSame($text1, $text2);
    }

    public function test_null_sector_does_not_crash(): void
    {
        $text = $this->service->generatePersonalizedText('SAP AG', null, null);
        $this->assertNotEmpty($text);
    }

    public function test_text_contains_german_keywords(): void
    {
        $text = $this->service->generatePersonalizedText('Volkswagen AG');
        // At least one German professional word should appear in the fallback text
        $germanWords = ['Unternehmen', 'Entwickler', 'Fachinformatiker', 'Technologie', 'Bewerber', 'Kenntnisse'];
        $found = false;
        foreach ($germanWords as $word) {
            if (str_contains($text, $word)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Text should contain at least one German professional keyword");
    }

    public function test_text_does_not_contain_html_tags(): void
    {
        $text = $this->service->generatePersonalizedText('Allianz SE');
        $this->assertSame($text, strip_tags($text));
    }

    public function test_fallback_text_is_detailed(): void
    {
        $text = $this->service->generatePersonalizedText('Beispiel GmbH', 'Softwareentwicklung');
        $this->assertGreaterThanOrEqual(150, str_word_count($text));
    }
}
