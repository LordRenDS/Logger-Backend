<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test root redirects unauthenticated user to register.
     */
    public function test_root_redirects_unauthenticated_to_register(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/register');
    }

    /**
     * Test root redirects authenticated user to dashboard.
     */
    public function test_root_redirects_authenticated_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    }

    /**
     * Test login and registration pages are accessible.
     */
    public function test_auth_pages_are_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->get('/register');
        $response->assertStatus(200);
    }
}
