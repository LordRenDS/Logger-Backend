<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pc;
use App\Models\Process;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PcActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_pc_activities()
    {
        $user = User::factory()->create();
        $pc = Pc::factory()->create(['user_id' => $user->id]);
        Process::factory()->create(['pc_id' => $pc->id, 'process_name' => 'chrome.exe']);

        $this->actingAs($user);
        $response = $this->get(route('pcs.activities', $pc));

        $response->assertStatus(200);
        $response->assertViewIs('pcs.activities');
        $response->assertViewHas('pc', $pc);
    }

    public function test_user_cannot_view_others_pc_activities()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $pc = Pc::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);
        $response = $this->get(route('pcs.activities', $pc));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_pc_activities()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $pc = Pc::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin);
        $response = $this->get(route('pcs.activities', $pc));

        $response->assertStatus(200);
        $response->assertViewIs('pcs.activities');
    }

    public function test_activities_can_be_filtered_by_process_name()
    {
        $user = User::factory()->create();
        $pc = Pc::factory()->create(['user_id' => $user->id]);
        Process::factory()->create(['pc_id' => $pc->id, 'process_name' => 'chrome.exe']);
        Process::factory()->create(['pc_id' => $pc->id, 'process_name' => 'notepad.exe']);

        $this->actingAs($user);
        $response = $this->get(route('pcs.activities', [$pc, 'process_name' => 'chrome']));

        $response->assertStatus(200);
        $activities = $response->viewData('activities');
        $this->assertCount(1, $activities);
        $this->assertEquals('chrome.exe', $activities[0]->process_name);
    }
}
