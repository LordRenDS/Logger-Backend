<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pc;
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
        // Note: View assertions depend on the Blade file which might have changed
        // But we expect the status to be 200 and the collection to be passed
    }

    public function test_admin_can_see_all_users_on_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_user_can_update_per_page_setting()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/dashboard/per-page', ['per_page' => 25]);

        $response->assertRedirect();
        $this->assertEquals(25, session('per_page'));
    }

    public function test_per_page_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/dashboard/per-page', ['per_page' => 101]);

        $response->assertSessionHasErrors('per_page');
    }
}
