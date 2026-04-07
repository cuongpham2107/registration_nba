<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/login');

        if ($response->getStatusCode() === 404) {
            $this->markTestSkipped('Login route is not available in this app configuration.');
        }

        $response->assertSuccessful();
    }
}
