# TSV Export Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add TSV data export functionality for PC activities and statuses to the dashboard.

**Architecture:** We will add `export` methods to `PcActivityController` and `PcStatusController` that stream TSV data using `StreamedResponse` and a database `cursor()`. We'll also add export buttons to the corresponding blade views.

**Tech Stack:** PHP 8.4, Laravel 12.x, Blade, TailwindCSS.

---

### Task 1: Add Routes for Export

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing test for Activity Export**
Add to `tests/Feature/PcActivityTest.php` inside `PcActivityTest` class:
```php
    public function test_user_can_export_pc_activities()
    {
        $user = \App\Models\User::factory()->create();
        $pc = \App\Models\Pc::factory()->create(['user_id' => $user->id]);
        \App\Models\Process::factory()->count(3)->create(['pc_id' => $pc->id]);

        $response = $this->actingAs($user)->get(route('pcs.activities.export', $pc));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/tab-separated-values; charset=UTF-8');
    }
```

- [ ] **Step 2: Write the failing test for Status Export**
Add to `tests/Feature/PcStatusTest.php` inside `PcStatusTest` class:
```php
    public function test_user_can_export_pc_statuses()
    {
        $user = \App\Models\User::factory()->create();
        $pc = \App\Models\Pc::factory()->create(['user_id' => $user->id]);
        $schedule = \App\Models\Schedule::factory()->create(['pc_id' => $pc->id]);
        \App\Models\PcStatus::factory()->create(['schedule_id' => $schedule->id, 'status' => 'On']);

        $response = $this->actingAs($user)->get(route('pcs.statuses.export', $pc));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/tab-separated-values; charset=UTF-8');
    }
```

- [ ] **Step 3: Run tests to verify they fail**
Run: `docker compose exec -u www-data app php artisan test --filter test_user_can_export_pc_activities`
Expected: FAIL (Route not defined)

- [ ] **Step 4: Write minimal implementation**
Modify `routes/web.php`. Inside the `Route::middleware(['auth'])->group(function () {` block, next to the existing resource routes for PCs:
```php
    Route::get('/pcs/{pc}/activities/export', [PcActivityController::class, 'export'])->name('pcs.activities.export');
    Route::get('/pcs/{pc}/activities', [PcActivityController::class, 'index'])->name('pcs.activities');
    
    Route::get('/pcs/{pc}/statuses/export', [PcStatusController::class, 'export'])->name('pcs.statuses.export');
    Route::get('/pcs/{pc}/statuses', [PcStatusController::class, 'index'])->name('pcs.statuses');
```

- [ ] **Step 5: Run tests again**
Expected: FAIL (Method export does not exist)

- [ ] **Step 6: Commit**
```bash
git add tests/Feature/PcActivityTest.php tests/Feature/PcStatusTest.php routes/web.php
git commit -m "feat: add tsv export routes and tests"
```

---

### Task 2: Implement Controller Export Methods

**Files:**
- Modify: `app/Http/Controllers/Web/PcActivityController.php`
- Modify: `app/Http/Controllers/Web/PcStatusController.php`

- [ ] **Step 1: Implement PcActivityController@export**
Add to `PcActivityController`:
```php
    public function export(Request $request, Pc $pc)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $pc->user_id) {
            abort(403, 'Unauthorized access to this PC.');
        }

        $fileName = 'pc_' . $pc->id . '_activities_' . now()->format('Y_m_d_His') . '.tsv';

        $headers = [
            'Content-Type' => 'text/tab-separated-values; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->stream(function () use ($pc) {
            $handle = fopen('php://output', 'w');
            // Write BOM for Excel compatibility with UTF-8
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Start Time', 'Process', 'Window Title', 'Duration (HH:MM:SS)'], "\t");

            foreach ($pc->processes()->cursor() as $process) {
                fputcsv($handle, [
                    $process->process_start->format('Y-m-d H:i:s'),
                    $process->process_name,
                    $process->window_name,
                    gmdate("H:i:s", $process->duration)
                ], "\t");
            }
            fclose($handle);
        }, 200, $headers);
    }
```

- [ ] **Step 2: Implement PcStatusController@export**
Add to `PcStatusController`:
```php
    public function export(Request $request, Pc $pc)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $pc->user_id) {
            abort(403, 'Unauthorized access to this PC.');
        }

        $fileName = 'pc_' . $pc->id . '_statuses_' . now()->format('Y_m_d_His') . '.tsv';

        $headers = [
            'Content-Type' => 'text/tab-separated-values; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->stream(function () use ($pc) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Timestamp', 'Status'], "\t");

            foreach ($pc->schedules()->with('pcStatus')->cursor() as $schedule) {
                fputcsv($handle, [
                    $schedule->timestamp ? $schedule->timestamp->format('Y-m-d H:i:s') : 'N/A',
                    $schedule->pcStatus?->status ?? 'Unknown'
                ], "\t");
            }
            fclose($handle);
        }, 200, $headers);
    }
```

- [ ] **Step 3: Run tests to verify they pass**
Run: `docker compose exec -u www-data app php artisan test --filter test_user_can_export_pc_activities`
Run: `docker compose exec -u www-data app php artisan test --filter test_user_can_export_pc_statuses`
Expected: PASS

- [ ] **Step 4: Commit**
```bash
git add app/Http/Controllers/Web/PcActivityController.php app/Http/Controllers/Web/PcStatusController.php
git commit -m "feat: implement tsv export logic in controllers"
```

---

### Task 3: Update UI to include Export Buttons

**Files:**
- Modify: `resources/views/pcs/activities.blade.php`
- Modify: `resources/views/pcs/statuses.blade.php`

- [ ] **Step 1: Update activities.blade.php**
Find the header section around line 5-11:
```html
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ auth()->user()->role === 'admin' ? route('admin.users.show', $pc->user_id) : route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block">&larr; Back to Devices</a>
            <h1 class="text-3xl font-bold text-gray-900">Activities: {{ $pc->name ?? $pc->unique_id }}</h1>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('pcs.activities.export', $pc) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                Export to TSV
            </a>
            <x-per-page-selector />
        </div>
    </div>
```

- [ ] **Step 2: Update statuses.blade.php**
Find the header section around line 5-9:
```html
    <div class="flex justify-between items-center">
        <div>
            <a href="{{ auth()->user()->role === 'admin' ? route('admin.users.show', $pc->user_id) : route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-900 mb-2 inline-block">&larr; Back to Devices</a>
            <h1 class="text-3xl font-bold text-gray-900">Status History: {{ $pc->name ?? $pc->unique_id }}</h1>
        </div>
        <div>
            <a href="{{ route('pcs.statuses.export', $pc) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                Export to TSV
            </a>
        </div>
    </div>
```

- [ ] **Step 3: Commit**
```bash
git add resources/views/pcs/activities.blade.php resources/views/pcs/statuses.blade.php
git commit -m "feat: add tsv export buttons to views"
```
