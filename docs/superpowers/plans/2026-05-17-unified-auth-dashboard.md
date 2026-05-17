# Unified Auth, Admin Setup, and Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement automated admin creation via env variables, integrate Laravel Breeze for web authentication, and unify the dashboard into a single role-aware endpoint.

**Architecture:** Use Laravel Breeze for standard session-based auth. Admin credentials will be injected from `db.env` into the `app` container and processed by the `DatabaseSeeder`. The dashboard will use a single controller method that branches logic based on the user's role.

**Tech Stack:** PHP 8.4, Laravel 12, PostgreSQL 18, TailwindCSS, Laravel Breeze.

---

### Task 0: Branching Setup

- [ ] **Step 1: Create the `dev` branch**
Run: `git checkout -b dev`

- [ ] **Step 2: Commit initial branch state**
Run: `git commit --allow-empty -m "init: start dev branch"`

---

### Task 1: Admin Auto-Creation via env

**Files:**
- Modify: `db.env`
- Modify: `docker-compose.yml`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create feature branch**
Run: `git checkout -b feature/admin-setup dev`

- [ ] **Step 2: Update `db.env` with admin credentials**
Add to `db.env`:
```env
ADMIN_NAME=Admin
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=admin_password
```

- [ ] **Step 3: Update `docker-compose.yml` to inject `db.env` into `app`**
Modify `docker-compose.yml`:
```yaml
  app:
    # ...
    env_file:
      - .env
      - db.env  # Add this line
```

- [ ] **Step 4: Update `DatabaseSeeder.php`**
Modify `database/seeders/DatabaseSeeder.php`:
```php
public function run(): void
{
    $this->call([
        PcStatusSeeder::class,
    ]);

    if (env('ADMIN_EMAIL')) {
        \App\Models\User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => \Illuminate\Support\Facades\Hash::make(env('ADMIN_PASSWORD')),
                'role' => 'admin',
            ]
        );
    }
}
```

- [ ] **Step 5: Verify admin creation**
Run: `php artisan migrate:fresh --seed`
Run: `php artisan tinker --execute="print(App\Models\User::where('role', 'admin')->count())"`
Expected: `1`

- [ ] **Step 6: Commit changes**
Run: `git add . && git commit -m "feat: implement admin auto-creation via env variables"`

---

### Task 2: Auth Integration (Laravel Breeze) & Redirects

**Files:**
- Modify: `composer.json`
- Modify: `routes/web.php`
- New: `tests/Feature/WebAuthTest.php`

- [ ] **Step 1: Create feature branch**
Run: `git checkout -b feature/auth-breeze dev`

- [ ] **Step 2: Install Laravel Breeze**
Run: `composer require laravel/breeze --dev`
Run: `php artisan breeze:install blade` (Follow prompts if any, usually non-interactive)

- [ ] **Step 3: Update `routes/web.php` for root redirect**
Modify `routes/web.php`:
```php
Route::get('/', function () {
    return redirect()->route('dashboard');
});
```

- [ ] **Step 4: Create `tests/Feature/WebAuthTest.php`**
```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login_for_guests(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_and_registration_links_exist(): void
    {
        $response = $this->get('/login');
        $response->assertSee('Register');
        
        $response = $this->get('/register');
        $response->assertSee('Already registered?');
    }
}
```

- [ ] **Step 5: Run tests**
Run: `php artisan test tests/Feature/WebAuthTest.php`
Expected: PASS

- [ ] **Step 6: Commit changes**
Run: `git add . && git commit -m "feat: install laravel breeze and setup root redirect"`

---

### Task 3: Unified Dashboard

**Files:**
- Modify: `app/Http/Controllers/Web/DashboardController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/dashboard.blade.php`
- New: `tests/Feature/DashboardTest.php`

- [ ] **Step 1: Create feature branch**
Run: `git checkout -b feature/unified-dashboard dev`

- [ ] **Step 2: Refactor `DashboardController.php`**
```php
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
```

- [ ] **Step 3: Consolidate `routes/web.php`**
Remove `/admin/dashboard` route. Ensure `/dashboard` is used for all roles.

- [ ] **Step 4: Refactor `dashboard.blade.php`**
Merge logic from `admin/dashboard.blade.php` into `dashboard.blade.php` using `@if(Auth::user()->role === 'admin')`.

- [ ] **Step 5: Create `tests/Feature/DashboardTest.php`**
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_all_users_on_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_user_sees_only_own_pcs_on_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee($otherUser->name);
    }
}
```

- [ ] **Step 6: Run tests**
Run: `php artisan test tests/Feature/DashboardTest.php`
Expected: PASS

- [ ] **Step 7: Commit changes**
Run: `git add . && git commit -m "feat: unify dashboard endpoint for all roles"`

---

### Task 4: Final Cleanup & Merging

- [ ] **Step 1: Merge feature branches into `dev`**
Run: `git checkout dev`
Run: `git merge feature/admin-setup feature/auth-breeze feature/unified-dashboard`

- [ ] **Step 2: Remove redundant files**
Run: `rm resources/views/admin/dashboard.blade.php` (if not already handled)
Run: `rm resources/views/welcome.blade.php`

- [ ] **Step 3: Run all tests**
Run: `php artisan test`

- [ ] **Step 4: Commit final state**
Run: `git add . && git commit -m "chore: cleanup and final merge to dev"`
