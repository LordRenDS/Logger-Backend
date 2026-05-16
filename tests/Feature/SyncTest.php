<?php

namespace Tests\Feature;

use App\Models\Pc;
use App\Models\PcStatus;
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
        
        // Seed statuses
        PcStatus::create(['status' => 'on']);
        PcStatus::create(['status' => 'off']);
    }

    /**
     * Test syncing processes.
     */
    public function test_can_sync_processes(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/sync/processes', [
                'pc_unique_id' => 'pc-123',
                'pc_name' => 'Work PC',
                'data' => [
                    [
                        'process_start' => now()->toDateTimeString(),
                        'process_name' => 'chrome.exe',
                        'window_name' => 'Google Search',
                        'duration' => 60,
                    ],
                    [
                        'process_start' => now()->addMinutes(5)->toDateTimeString(),
                        'process_name' => 'devenv.exe',
                        'window_name' => 'Visual Studio',
                        'duration' => 300,
                    ],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['count' => 2]);

        $this->assertDatabaseHas('pcs', ['unique_id' => 'pc-123', 'user_id' => $this->user->id]);
        $this->assertDatabaseCount('processes', 2);
    }

    /**
     * Test syncing schedules.
     */
    public function test_can_sync_schedules(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/sync/schedules', [
                'pc_unique_id' => 'pc-123',
                'data' => [
                    [
                        'timestamp' => now()->toDateTimeString(),
                        'status' => 'on',
                    ],
                    [
                        'timestamp' => now()->addHours(8)->toDateTimeString(),
                        'status' => 'off',
                    ],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson(['count' => 2]);

        $this->assertDatabaseCount('schedules', 2);
    }
}
