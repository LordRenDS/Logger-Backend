<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_their_pcs_on_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Your Devices');
        $response->assertDontSee('Admin Dashboard');
    }

    public function test_admin_can_see_all_users_on_dashboard()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard - All Users');
        $response->assertDontSee('Your Devices');
    }

    public function test_admin_dashboard_route_is_removed()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(404);
    }
}
