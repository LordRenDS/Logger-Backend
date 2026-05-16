<?php

namespace Tests\Feature;

use App\Models\Pc;
use App\Models\User;
use App\Services\PcService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PcServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $pcService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pcService = new PcService();
    }

    public function test_it_creates_a_new_pc_if_not_exists()
    {
        $user = User::factory()->create();
        $uniqueId = 'PC-UNIQUE-123';
        $name = 'Test PC';

        $pc = $this->pcService->findOrCreatePc($user, $uniqueId, $name);

        $this->assertInstanceOf(Pc::class, $pc);
        $this->assertEquals($uniqueId, $pc->unique_id);
        $this->assertEquals($name, $pc->name);
        $this->assertEquals($user->id, $pc->user_id);
        $this->assertDatabaseHas('pcs', ['unique_id' => $uniqueId]);
    }

    public function test_it_updates_existing_pc_if_exists()
    {
        $user = User::factory()->create();
        $existingPc = Pc::create([
            'user_id' => $user->id,
            'unique_id' => 'PC-EXISTING',
            'name' => 'Old Name',
            'last_seen_at' => now()->subDay(),
        ]);

        $newName = 'New Name';
        $pc = $this->pcService->findOrCreatePc($user, 'PC-EXISTING', $newName);

        $this->assertEquals($existingPc->id, $pc->id);
        $this->assertEquals($newName, $pc->name);
        $this->assertTrue($pc->last_seen_at->isToday());
        $this->assertDatabaseHas('pcs', ['id' => $existingPc->id, 'name' => $newName]);
    }
}
