<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_update_per_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/dashboard/per-page', ['per_page' => 25]);
        
        echo "\nStatus: " . $response->status() . "\n";
        if ($response->status() !== 302) {
            echo "Content: " . $response->getContent() . "\n";
        }
        
        $response->assertRedirect();
    }
}
