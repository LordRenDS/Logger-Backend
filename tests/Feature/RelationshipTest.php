<?php

namespace Tests\Feature;

use App\Models\Pc;
use App\Models\Process;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test User to PC relationship.
     */
    public function test_user_has_pcs(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $user->id,
            'unique_id' => 'pc-123',
            'name' => 'Work PC',
        ]);

        $this->assertCount(1, $user->pcs);
        $this->assertEquals('pc-123', $user->pcs->first()->unique_id);
    }

    /**
     * Test PC to Process relationship.
     */
    public function test_pc_has_processes(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $pc = Pc::create([
            'user_id' => $user->id,
            'unique_id' => 'pc-123',
        ]);

        $process = Process::create([
            'pc_id' => $pc->id,
            'process_start' => now(),
            'process_name' => 'chrome.exe',
            'window_name' => 'Google Search',
            'duration' => 60,
        ]);

        $this->assertCount(1, $pc->processes);
        $this->assertEquals('chrome.exe', $pc->processes->first()->process_name);
    }
}
