# Admin Auto-Creation via env Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Automatically create an admin user during database seeding using credentials from environment variables.

**Architecture:** Inject `db.env` into the `app` container via Docker Compose. Update `DatabaseSeeder.php` to read `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` from the environment and create the user if it doesn't exist.

**Tech Stack:** Laravel, Docker, PHP.

---

### Task 1: Setup and Configuration

**Files:**
- Modify: `db.env.example`
- Modify: `docker-compose.yml`

- [ ] **Step 1: Create feature branch**
Run: `git checkout -b feature/admin-setup dev`

- [ ] **Step 2: Add admin credentials to `db.env.example`**
Add placeholder admin credentials to the example file.

- [ ] **Step 3: Update `docker-compose.yml` to inject `db.env` into `app`**
Add `db.env` to the `env_file` list for the `app` service.

- [ ] **Step 4: Commit configuration changes**
Run: `git add db.env.example docker-compose.yml && git commit -m "- add admin env variables to example and inject db.env into app"`

### Task 2: Database Seeder Implementation

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Update `DatabaseSeeder.php` to create admin user**
Implement logic to create an admin user using `env('ADMIN_NAME')`, `env('ADMIN_EMAIL')`, and `env('ADMIN_PASSWORD')`.

- [ ] **Step 2: Run migrations and seeders**
Run: `php artisan migrate:fresh --seed`

- [ ] **Step 3: Verify user existence via Tinker**
Run: `php artisan tinker --execute="print_r(\App\Models\User::where('email', env('ADMIN_EMAIL'))->first()?->toArray())"`

- [ ] **Step 4: Commit seeder changes**
Run: `git add database/seeders/DatabaseSeeder.php && git commit -m "- update databaseseeder to create admin user from env variables"`
