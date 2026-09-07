<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_public_pages_include_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_health_endpoint_reports_final_capabilities(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('release', 'batch-11')
            ->assertJsonPath('worker_protocol', 'V5');
    }
}
