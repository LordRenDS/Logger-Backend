<?php

namespace Tests\Feature;

use App\Models\Pc;
use App\Models\PcStatus;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PcStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default statuses as is expected in schedules
        PcStatus::create(['status' => 'on']);
        PcStatus::create(['status' => 'off']);
    }

    public function test_user_can_view_their_pc_statuses()
    {
        $user = User::factory()->create();
        $pc = Pc::factory()->create(['user_id' => $user->id]);

        $statusOn = PcStatus::where('status', 'on')->first();
        Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOn->id,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('pcs.statuses', $pc));

        $response->assertStatus(200);
        $response->assertViewIs('pcs.statuses');
        $response->assertViewHas('pc', $pc);
        $this->assertCount(1, $response->viewData('statuses'));
    }

    public function test_user_cannot_view_others_pc_statuses()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $pc = Pc::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);
        $response = $this->get(route('pcs.statuses', $pc));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_pc_statuses()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $pc = Pc::factory()->create(['user_id' => $user->id]);

        $statusOff = PcStatus::where('status', 'off')->first();
        Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOff->id,
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('pcs.statuses', $pc));

        $response->assertStatus(200);
        $response->assertViewIs('pcs.statuses');
    }

    public function test_user_can_export_pc_statuses()
    {
        $user = User::factory()->create();
        $pc = Pc::factory()->create(['user_id' => $user->id]);
        $statusOn = PcStatus::where('status', 'on')->first();
        Schedule::factory()->create(['pc_id' => $pc->id, 'pc_status_id' => $statusOn->id]);

        $this->actingAs($user);
        $response = $this->get(route('pcs.statuses.export', $pc));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/tab-separated-values; charset=UTF-8');
    }
}
