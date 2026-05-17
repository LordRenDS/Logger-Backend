# PC Discovery and Data Sync Service Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement PcService, SyncService, SyncController and routes to enable PC discovery and batch data synchronization.

**Architecture:** Service-Controller pattern. PcService handles PC lifecycle, SyncService handles batch data processing, and SyncController manages API requests.

**Tech Stack:** Laravel (PHP), Eloquent, JWT Auth.

---

### Task 1: Implement PcService

**Files:**
- Create: `app/Services/PcService.php`
- Test: `tests/Feature/PcServiceTest.php`

- [ ] **Step 1: Create PcService with findOrCreatePc method**
```php
<?php

namespace App\Services;

use App\Models\Pc;
use App\Models\User;
use Carbon\Carbon;

class PcService
{
    public function findOrCreatePc(User $user, string $uniqueId, ?string $name = null): Pc
    {
        $pc = Pc::where('unique_id', $uniqueId)->first();

        if ($pc) {
            $pc->update([
                'last_seen_at' => Carbon::now(),
                'name' => $name ?? $pc->name,
                'user_id' => $user->id, // Re-assign if necessary or keep existing? Task says associate with provided user.
            ]);
        } else {
            $pc = Pc::create([
                'user_id' => $user->id,
                'unique_id' => $uniqueId,
                'name' => $name,
                'last_seen_at' => Carbon::now(),
            ]);
        }

        return $pc;
    }
}
```

- [ ] **Step 2: Create a test to verify PcService**
- [ ] **Step 3: Run the test**
- [ ] **Step 4: Commit**

### Task 2: Implement SyncService

**Files:**
- Create: `app/Services/SyncService.php`
- Test: `tests/Feature/SyncServiceTest.php`

- [ ] **Step 1: Create SyncService with syncProcesses and syncSchedules methods**
```php
<?php

namespace App\Services;

use App\Models\Pc;
use App\Models\PcStatus;
use App\Models\Process;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class SyncService
{
    public function syncProcesses(Pc $pc, array $data): int
    {
        $records = array_map(function ($item) use ($pc) {
            return [
                'pc_id' => $pc->id,
                'process_start' => $item['process_start'],
                'process_name' => $item['process_name'],
                'window_name' => $item['window_name'] ?? '',
                'duration' => $item['duration'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $data);

        Process::insert($records);

        return count($records);
    }

    public function syncSchedules(Pc $pc, array $data): int
    {
        $statuses = PcStatus::all()->pluck('id', 'status');

        $records = array_map(function ($item) use ($pc, $statuses) {
            return [
                'pc_id' => $pc->id,
                'timestamp' => $item['timestamp'],
                'pc_status_id' => $statuses[$item['status']] ?? $statuses['off'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $data);

        Schedule::insert($records);

        return count($records);
    }
}
```

- [ ] **Step 2: Create a test to verify SyncService**
- [ ] **Step 3: Run the test**
- [ ] **Step 4: Commit**

### Task 3: Implement SyncController

**Files:**
- Create: `app/Http/Controllers/Api/v1/SyncController.php`

- [ ] **Step 1: Create SyncController with Swagger annotations and logic**
```php
<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\PcService;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncController extends Controller
{
    protected $pcService;
    protected $syncService;

    public function __construct(PcService $pcService, SyncService $syncService)
    {
        $this->pcService = $pcService;
        $this->syncService = $syncService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sync/processes",
     *     summary="Sync process activity data",
     *     tags={"Sync"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"pc_unique_id", "data"},
     *             @OA\Property(property="pc_unique_id", type="string", example="PC-12345"),
     *             @OA\Property(property="pc_name", type="string", example="My Desktop"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="process_start", type="string", format="date-time"),
     *                 @OA\Property(property="process_name", type="string"),
     *                 @OA\Property(property="window_name", type="string"),
     *                 @OA\Property(property="duration", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful sync")
     * )
     */
    public function syncProcesses(Request $request)
    {
        $validated = $request->validate([
            'pc_unique_id' => 'required|string',
            'pc_name' => 'nullable|string',
            'data' => 'required|array',
            'data.*.process_start' => 'required|date',
            'data.*.process_name' => 'required|string',
            'data.*.window_name' => 'nullable|string',
            'data.*.duration' => 'required|integer',
        ]);

        $pc = $this->pcService.findOrCreatePc(Auth::user(), $validated['pc_unique_id'], $validated['pc_name'] ?? null);
        $count = $this->syncService.syncProcesses($pc, $validated['data']);

        return response()->json([
            'message' => 'Processes synced successfully',
            'count' => $count,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sync/schedules",
     *     summary="Sync PC schedule data (on/off)",
     *     tags={"Sync"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"pc_unique_id", "data"},
     *             @OA\Property(property="pc_unique_id", type="string", example="PC-12345"),
     *             @OA\Property(property="pc_name", type="string", example="My Desktop"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="timestamp", type="string", format="date-time"),
     *                 @OA\Property(property="status", type="string", enum={"on", "off"})
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful sync")
     * )
     */
    public function syncSchedules(Request $request)
    {
        $validated = $request->validate([
            'pc_unique_id' => 'required|string',
            'pc_name' => 'nullable|string',
            'data' => 'required|array',
            'data.*.timestamp' => 'required|date',
            'data.*.status' => 'required|string|in:on,off',
        ]);

        $pc = $this->pcService.findOrCreatePc(Auth::user(), $validated['pc_unique_id'], $validated['pc_name'] ?? null);
        $count = $this->syncService.syncSchedules($pc, $validated['data']);

        return response()->json([
            'message' => 'Schedules synced successfully',
            'count' => $count,
        ]);
    }
}
```

### Task 4: Register Routes

**Files:**
- Modify: `routes/api.php`

- [ ] **Step 1: Add sync routes under v1 prefix**
```php
    Route::middleware('auth:api')->prefix('sync')->group(function () {
        Route::post('processes', [SyncController::class, 'syncProcesses']);
        Route::post('schedules', [SyncController::class, 'syncSchedules']);
    });
```

- [ ] **Step 2: Commit**

### Task 5: Final Verification

- [ ] **Step 1: Run all tests**
- [ ] **Step 2: Final commit**
