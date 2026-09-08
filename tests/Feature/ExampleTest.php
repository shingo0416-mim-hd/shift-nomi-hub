<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_shows_the_service_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('ShiftHub');
        $response->assertSee('シフトづくりを、');
        $response->assertSee(route('login'));
    }
}
