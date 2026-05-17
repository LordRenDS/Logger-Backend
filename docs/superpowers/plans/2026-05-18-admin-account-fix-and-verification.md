# Fix Admin Account Creation and Docker Verification Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ensure the admin account is automatically created on startup via Docker and verify the entire system works from scratch.

**Architecture:** Use Laravel Seeders triggered by the Docker entrypoint script. Environment variables for the admin account will be shared between services.

**Tech Stack:** PHP 8.4 (Laravel 12), Docker, PostgreSQL.

---

### Task 1: Research and Environment Prep

**Files:**
- Modify: `docker-compose.yml`
- Check: `db.env` (Ensure it contains `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`)

- [ ] **Step 1: Update docker-compose.yml to share db.env with app service**

```yaml
<<<<
    env_file:
      - .env
====
    env_file:
      - .env
      - db.env
>>>>
```

- [ ] **Step 2: Verify db.env exists and has required variables (locally)**
Run: `ls db.env` and check if it has the variables (don't print values).

- [ ] **Step 3: Commit environment changes**

```bash
git add docker-compose.yml
git commit -m "chore: share db.env with app service for admin credentials"
```

### Task 2: Implement Admin Seeding

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Add admin creation logic to DatabaseSeeder.php**

```php
    public function run(): void
    {
        $this->call([
            PcStatusSeeder::class,
        ]);

        $adminName = env('ADMIN_NAME');
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if ($adminName && $adminEmail && $adminPassword) {
            \App\Models\User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => \Illuminate\Support\Facades\Hash::make($adminPassword),
                    'role' => 'admin',
                ]
            );
        }
    }
```

- [ ] **Step 2: Commit seeder changes**

```bash
git add database/seeders/DatabaseSeeder.php
git commit -m "feat: add idempotent admin creation to DatabaseSeeder"
```

### Task 3: Full Docker Verification (From Scratch)

- [ ] **Step 1: Stop and remove existing containers and volumes**
Run: `docker compose down -v`

- [ ] **Step 2: Build and start containers**
Run: `docker compose up --build -d`

- [ ] **Step 3: Wait for migrations and seeders to finish**
Run: `docker compose logs -f app` (Watch for "Running migrations and seeders...")

- [ ] **Step 4: Verify admin user in database**
Run: `docker compose exec db psql -U ${POSTGRES_USER} -d ${POSTGRES_DB} -c "SELECT name, email, role FROM users;"`
(Note: Replace variables if they are not in your environment, or use `docker compose exec app php artisan tinker --execute="print_r(App\Models\User::where('role', 'admin')->first()?->toArray())"`)

- [ ] **Step 5: Verify web login (Manual or via curl)**
Run: `curl -X POST http://localhost:8080/login -d "email=${ADMIN_EMAIL}&password=${ADMIN_PASSWORD}" -c cookies.txt -L`
Check if it redirects to `/dashboard`.

### Task 4: Verify on Existing Containers (Update)

- [ ] **Step 1: Run seeders manually on running containers**
Run: `docker compose exec app php artisan db:seed`

- [ ] **Step 2: Verify admin user creation**
Run: `docker compose exec app php artisan tinker --execute="echo App\Models\User::where('role', 'admin')->count()"`

- [ ] **Step 3: Final Commit and Documentation Update**
Update `GEMINI.md` or `README.md` if necessary to reflect that `db.env` is now mandatory for `app`.

```bash
git add README.md GEMINI.md
git commit -m "docs: document admin auto-creation via db.env"
```
