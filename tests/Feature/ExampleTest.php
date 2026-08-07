<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Basic smoke test: the application boots and the root route responds.
 * The root redirects to /entreprises which requires auth → 302 is expected.
 */
class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_redirect_for_unauthenticated_root(): void
    {
        $response = $this->get('/');
        // Root redirects to entreprises.index → auth middleware → login
        $response->assertRedirect();
    }
}
