<?php

namespace Tests\Feature;

use App\Models\Pc;
use App\Models\PcStatus;
use App\Models\User;
use App\Models\Process;
use App\Models\Schedule;
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
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/pcs', [
                'unique_id' => 'pc-123',
                'name' => 'Work PC',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.unique_id', 'pc-123');

        // List PCs
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
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

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/pcs/{$pc->unique_id}/processes", [
                'data' => [
                   [
                       'process_start' => now()->toDateTimeString(),
                       'process_name' => 'chrome.exe',
                       'window_name' => 'Google Search',
                       'duration' => 60,
                   ]
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

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/pcs/{$pc->unique_id}/schedules", [
                'data' => [
                   [
                       'timestamp' => now()->toDateTimeString(),
                       'status' => 'on',
                   ]
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

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/pcs/{$pc->unique_id}");

        $response->assertStatus(403);
    }
}
