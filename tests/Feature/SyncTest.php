<?php

namespace Tests\Feature;

use App\Models\Pc;
use App\Models\PcStatus;
use App\Models\Process;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('access_token');

        PcStatus::create(['status' => 'on']);
        PcStatus::create(['status' => 'off']);
    }

    public function test_can_register_and_list_pcs(): void
    {
        // Register PC
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/v1/pcs', [
                'unique_id' => 'pc-123',
                'name' => 'Work PC',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.unique_id', 'pc-123');

        // List PCs
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/v1/pcs');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_sync_processes_under_pc(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-123',
            'name' => 'Work PC',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/pcs/{$pc->unique_id}/processes", [
                'data' => [
                    [
                        'process_start' => now()->toDateTimeString(),
                        'process_name' => 'chrome.exe',
                        'window_name' => 'Google Search',
                        'duration' => 60,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson(['count' => 1]);

        $this->assertDatabaseCount('processes', 1);
    }

    public function test_can_sync_schedules_under_pc(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-123',
            'name' => 'Work PC',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/pcs/{$pc->unique_id}/schedules", [
                'data' => [
                    [
                        'timestamp' => now()->toDateTimeString(),
                        'status' => 'on',
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson(['count' => 1]);

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_cannot_access_other_users_pc(): void
    {
        $otherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $otherUser->id,
            'unique_id' => 'pc-other',
            'name' => 'Jane PC',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/pcs/{$pc->unique_id}");

        $response->assertStatus(403);
    }

    // 1. PC CRUD and Authorization
    public function test_can_show_pc(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-show',
            'name' => 'Show PC',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/pcs/{$pc->unique_id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.unique_id', 'pc-show')
            ->assertJsonPath('data.name', 'Show PC');
    }

    public function test_can_update_pc(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-update',
            'name' => 'Old Name',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/v1/pcs/{$pc->unique_id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('pcs', [
            'unique_id' => 'pc-update',
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_pc(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-delete',
            'name' => 'Delete PC',
            'last_seen_at' => now(),
        ]);

        $process = Process::create([
            'pc_id' => $pc->id,
            'process_start' => now(),
            'process_name' => 'explorer.exe',
            'window_name' => 'Desktop',
            'duration' => 10,
        ]);

        $statusOn = PcStatus::where('status', 'on')->first();
        $schedule = Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOn->id,
        ]);

        $this->assertDatabaseHas('pcs', ['id' => $pc->id]);
        $this->assertDatabaseHas('processes', ['id' => $process->id]);
        $this->assertDatabaseHas('schedules', ['id' => $schedule->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/v1/pcs/{$pc->unique_id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('pcs', ['id' => $pc->id]);
        $this->assertDatabaseMissing('processes', ['id' => $process->id]);
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_cannot_update_or_delete_other_users_pc(): void
    {
        $otherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $otherUser->id,
            'unique_id' => 'pc-other-crud',
            'name' => 'Jane PC',
            'last_seen_at' => now(),
        ]);

        // Try update
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/v1/pcs/{$pc->unique_id}", [
                'name' => 'Hacked Name',
            ]);
        $response->assertStatus(403);

        // Try delete
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/v1/pcs/{$pc->unique_id}");
        $response->assertStatus(403);
    }

    // 2. Process CRUD and Authorization
    public function test_can_list_nested_processes(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-list-proc',
            'name' => 'Proc List PC',
            'last_seen_at' => now(),
        ]);

        Process::create([
            'pc_id' => $pc->id,
            'process_start' => now(),
            'process_name' => 'a.exe',
            'window_name' => 'Win A',
            'duration' => 5,
        ]);

        Process::create([
            'pc_id' => $pc->id,
            'process_start' => now()->addMinutes(1),
            'process_name' => 'b.exe',
            'window_name' => 'Win B',
            'duration' => 15,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/pcs/{$pc->unique_id}/processes");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_cannot_list_nested_processes_of_other_users_pc(): void
    {
        $otherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $otherUser->id,
            'unique_id' => 'pc-other-proc',
            'name' => 'Jane PC',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/pcs/{$pc->unique_id}/processes");

        $response->assertStatus(403);
    }

    public function test_cannot_sync_processes_to_other_users_pc(): void
    {
        $otherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $otherUser->id,
            'unique_id' => 'pc-other-proc-sync',
            'name' => 'Jane PC',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/pcs/{$pc->unique_id}/processes", [
                'data' => [
                    [
                        'process_start' => now()->toDateTimeString(),
                        'process_name' => 'chrome.exe',
                        'window_name' => 'Google Search',
                        'duration' => 60,
                    ],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_can_show_process(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-show-proc',
            'name' => 'Proc Show PC',
            'last_seen_at' => now(),
        ]);

        $process = Process::create([
            'pc_id' => $pc->id,
            'process_start' => now(),
            'process_name' => 'chrome.exe',
            'window_name' => 'Chrome',
            'duration' => 30,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/processes/{$process->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.process_name', 'chrome.exe');
    }

    public function test_can_update_process(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-update-proc',
            'name' => 'Proc Update PC',
            'last_seen_at' => now(),
        ]);

        $process = Process::create([
            'pc_id' => $pc->id,
            'process_start' => now(),
            'process_name' => 'chrome.exe',
            'window_name' => 'Chrome',
            'duration' => 30,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/v1/processes/{$process->id}", [
                'process_name' => 'firefox.exe',
                'duration' => 50,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.process_name', 'firefox.exe')
            ->assertJsonPath('data.duration', 50);

        $this->assertDatabaseHas('processes', [
            'id' => $process->id,
            'process_name' => 'firefox.exe',
            'duration' => 50,
        ]);
    }

    public function test_can_delete_process(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-delete-proc',
            'name' => 'Proc Delete PC',
            'last_seen_at' => now(),
        ]);

        $process = Process::create([
            'pc_id' => $pc->id,
            'process_start' => now(),
            'process_name' => 'chrome.exe',
            'window_name' => 'Chrome',
            'duration' => 30,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/v1/processes/{$process->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('processes', ['id' => $process->id]);
    }

    public function test_cannot_crud_other_users_process(): void
    {
        $otherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $otherUser->id,
            'unique_id' => 'pc-other-proc-crud',
            'name' => 'Jane PC',
            'last_seen_at' => now(),
        ]);

        $process = Process::create([
            'pc_id' => $pc->id,
            'process_start' => now(),
            'process_name' => 'chrome.exe',
            'window_name' => 'Chrome',
            'duration' => 30,
        ]);

        // Try show
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/processes/{$process->id}");
        $response->assertStatus(403);

        // Try update
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/v1/processes/{$process->id}", [
                'process_name' => 'hacked.exe',
            ]);
        $response->assertStatus(403);

        // Try delete
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/v1/processes/{$process->id}");
        $response->assertStatus(403);
    }

    // 3. Schedule CRUD and Authorization
    public function test_can_list_nested_schedules(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-list-sched',
            'name' => 'Sched List PC',
            'last_seen_at' => now(),
        ]);

        $statusOn = PcStatus::where('status', 'on')->first();
        $statusOff = PcStatus::where('status', 'off')->first();

        Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOn->id,
        ]);

        Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now()->addMinutes(5),
            'pc_status_id' => $statusOff->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/pcs/{$pc->unique_id}/schedules");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_cannot_list_nested_schedules_of_other_users_pc(): void
    {
        $otherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $otherUser->id,
            'unique_id' => 'pc-other-sched',
            'name' => 'Jane PC',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/pcs/{$pc->unique_id}/schedules");

        $response->assertStatus(403);
    }

    public function test_cannot_sync_schedules_to_other_users_pc(): void
    {
        $otherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $otherUser->id,
            'unique_id' => 'pc-other-sched-sync',
            'name' => 'Jane PC',
            'last_seen_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson("/api/v1/pcs/{$pc->unique_id}/schedules", [
                'data' => [
                    [
                        'timestamp' => now()->toDateTimeString(),
                        'status' => 'on',
                    ],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_can_show_schedule(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-show-sched',
            'name' => 'Sched Show PC',
            'last_seen_at' => now(),
        ]);

        $statusOn = PcStatus::where('status', 'on')->first();
        $schedule = Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOn->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/schedules/{$schedule->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'on');
    }

    public function test_can_update_schedule(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-update-sched',
            'name' => 'Sched Update PC',
            'last_seen_at' => now(),
        ]);

        $statusOn = PcStatus::where('status', 'on')->first();
        $schedule = Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOn->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/v1/schedules/{$schedule->id}", [
                'status' => 'off',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'off');

        $statusOff = PcStatus::where('status', 'off')->first();
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'pc_status_id' => $statusOff->id,
        ]);
    }

    public function test_update_schedule_invalid_status_fails(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-update-sched-fail',
            'name' => 'Sched Update Fail PC',
            'last_seen_at' => now(),
        ]);

        $statusOn = PcStatus::where('status', 'on')->first();
        $schedule = Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOn->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/v1/schedules/{$schedule->id}", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
    }

    public function test_can_delete_schedule(): void
    {
        $pc = Pc::create([
            'user_id' => $this->user->id,
            'unique_id' => 'pc-delete-sched',
            'name' => 'Sched Delete PC',
            'last_seen_at' => now(),
        ]);

        $statusOn = PcStatus::where('status', 'on')->first();
        $schedule = Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOn->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/v1/schedules/{$schedule->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_cannot_crud_other_users_schedule(): void
    {
        $otherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $otherUser->id,
            'unique_id' => 'pc-other-sched-crud',
            'name' => 'Jane PC',
            'last_seen_at' => now(),
        ]);

        $statusOn = PcStatus::where('status', 'on')->first();
        $schedule = Schedule::create([
            'pc_id' => $pc->id,
            'timestamp' => now(),
            'pc_status_id' => $statusOn->id,
        ]);

        // Try show
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/v1/schedules/{$schedule->id}");
        $response->assertStatus(403);

        // Try update
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson("/api/v1/schedules/{$schedule->id}", [
                'status' => 'off',
            ]);
        $response->assertStatus(403);

        // Try delete
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson("/api/v1/schedules/{$schedule->id}");
        $response->assertStatus(403);
    }
}
