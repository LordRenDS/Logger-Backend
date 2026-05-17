# Unified Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a single dashboard entry point at `/dashboard` that displays either the user's PCs or the admin's user list based on the authenticated user's role.

**Architecture:** Combine logic in `DashboardController@index` and use conditional rendering in `dashboard.blade.php`. Remove redundant admin-specific routes and views.

**Tech Stack:** PHP 8.4, Laravel 12, Tailwind CSS.

---

### Task 1: Refactor `DashboardController`

**Files:**
- Modify: `app/Http/Controllers/Web/DashboardController.php`

- [ ] **Step 1: Update `index` method to handle roles and remove `adminIndex`**

Modify `app/Http/Controllers/Web/DashboardController.php`:
```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard: list own PCs or all users based on role.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $users = User::with(['pcs' => function ($query) {
                $query->withCount('processes');
            }])->get();

            return view('dashboard', compact('users'));
        }

        $pcs = $user->pcs()->with(['processes' => function ($query) {
            $query->latest('process_start')->limit(10);
        }])->get();

        return view('dashboard', compact('pcs'));
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Web/DashboardController.php
git commit -m "refactor: unify dashboard logic in DashboardController"
```

### Task 2: Update Routes and Layout

**Files:**
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step 1: Remove `/admin/dashboard` route**

In `routes/web.php`:
```php
<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Auth routes for session-based login/logout
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});
```

- [ ] **Step 2: Update `layouts/app.blade.php` navigation**

In `resources/views/layouts/app.blade.php`:
```blade
                    <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-500 text-sm font-medium text-gray-900">Dashboard</a>
                    </div>
```

- [ ] **Step 3: Commit**

```bash
git add routes/web.php resources/views/layouts/app.blade.php
git commit -m "feat: remove redundant admin dashboard route and update navigation"
```

### Task 3: Unified Dashboard View

**Files:**
- Modify: `resources/views/dashboard.blade.php`
- Delete: `resources/views/admin/dashboard.blade.php`

- [ ] **Step 1: Update `dashboard.blade.php` to handle both roles**

```blade
@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if(Auth::user()->role === 'admin')
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard - All Users</h1>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devices</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Activities</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->pcs->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->pcs->sum('processes_count') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <h1 class="text-3xl font-bold text-gray-900">Your Devices</h1>

        @foreach($pcs as $pc)
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $pc->name ?? 'Unnamed PC' }}</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">ID: {{ $pc->unique_id }} | Last seen: {{ $pc->last_seen_at?->diffForHumans() ?? 'Never' }}</p>
                    </div>
                </div>
                <div class="border-t border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Process</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Window Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pc->processes as $process)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $process->process_start->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $process->process_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">{{ $process->window_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ gmdate("H:i:s", $process->duration) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No activity recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
```

- [ ] **Step 2: Delete `resources/views/admin/dashboard.blade.php`**

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard.blade.php
git rm resources/views/admin/dashboard.blade.php
git commit -m "feat: unified dashboard view with conditional role rendering"
```

### Task 4: Verification with Tests

**Files:**
- Create: `tests/Feature/DashboardTest.php`

- [ ] **Step 1: Write the tests**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_their_pcs_on_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Your Devices');
        $response->assertDontSee('Admin Dashboard');
    }

    public function test_admin_can_see_all_users_on_dashboard()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard - All Users');
        $response->assertDontSee('Your Devices');
    }

    public function test_admin_dashboard_route_is_removed()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(404);
    }
}
```

- [ ] **Step 2: Run tests to verify**

Run: `php artisan test tests/Feature/DashboardTest.php`

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/DashboardTest.php
git commit -m "test: add role-based dashboard verification tests"
```
