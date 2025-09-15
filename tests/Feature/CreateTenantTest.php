<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_tenant_validation_errors(): void
    {
        $res = $this->postJson(route('tenants.store'), []);
        $res->assertStatus(422);
    }

    public function test_create_tenant_success(): void
    {
        $payload = [
            'tenant' => [
                'domain' => 'acme.localhost',
            ],
            'organization' => [
                'short_name' => 'ACME',
                'name' => 'ACME Inc',
                'category_name' => 'Default',
            ],
            'contacts' => [
                'email' => 'info@acme.test',
            ],
        ];

        $res = $this->postJson(route('tenants.store'), $payload);
        $res->assertStatus(200)
            ->assertJsonPath('tenant.id', fn ($v) => ! empty($v))
            ->assertJsonPath('domain.domain', 'acme.localhost');
    }
}
