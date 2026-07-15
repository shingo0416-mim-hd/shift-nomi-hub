<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_registration_routes_are_disabled(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertNotFound();
    }

    public function test_admin_api_registration_route_is_disabled(): void
    {
        $this->postJson('/api/admin/auth/register', [
            'tenant_name' => 'Tenant',
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertNotFound();
    }
}
