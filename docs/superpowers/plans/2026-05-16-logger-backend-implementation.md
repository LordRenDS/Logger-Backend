# Logger Backend Server Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Laravel 13 server with PostgreSQL 18, JWT authentication, and batch synchronization for PC activity data recorded by a WinForms client.

**Architecture:** Follows the Service-Controller-Resource pattern. All business logic is encapsulated in Services. JWT is used for stateless API authentication. PC entries are automatically discovered and linked to users upon data synchronization.

**Tech Stack:** Laravel 13.x, PHP 8.4, PostgreSQL 18, `php-open-source-saver/jwt-auth`, `darkaonline/l5-swagger`, Docker Compose (Apache + PG 18).

---

### Task 1: Project Scaffolding and Docker Setup

**Files:**
- Create: `docker-compose.yml`
- Create: `docker/php-server.Dockerfile`
- Create: `docker/server.conf`
- Create: `docker/docker-entrypoint.sh`
- Create: `docker/initdb.sh`
- Create: `.env.example`

- [ ] **Step 1: Scaffold Laravel 13 project**
Run: `composer create-project laravel/laravel . "^13.0"` (Note: if 13 is not yet on packagist, use `dev-master` or latest available, but the user requested 13 specifically, so we attempt that).
Expected: Laravel project structure created.

- [ ] **Step 2: Create Docker configuration files**
Create `docker-compose.yml` using PostgreSQL 18 and PHP 8.4 Apache.
Create supporting Dockerfiles and config as per the reference project.

- [ ] **Step 3: Setup .env and db.env**
Configure `.env` for PostgreSQL 18.

- [ ] **Step 4: Verify Docker build**
Run: `docker compose up -d --build`
Expected: Containers running, Laravel welcome page accessible at `http://localhost:8080`.

- [ ] **Step 5: Commit**
```bash
git add .
git commit -m "- project scaffolding with laravel 13 and docker setup"
```

---

### Task 2: Database Schema and Models

**Files:**
- Create: `database/migrations/2026_05_16_000001_create_pcs_table.php`
- Create: `database/migrations/2026_05_16_000002_create_pc_statuses_table.php`
- Create: `database/migrations/2026_05_16_000003_create_processes_table.php`
- Create: `database/migrations/2026_05_16_000004_create_schedules_table.php`
- Modify: `app/Models/User.php`
- Create: `app/Models/Pc.php`, `app/Models/PcStatus.php`, `app/Models/Process.php`, `app/Models/Schedule.php`

- [ ] **Step 1: Create migrations for all tables**
Define schema as per design spec (PostgreSQL 18 compatible).

- [ ] **Step 2: Implement Models and Relationships**
Add `hasMany` relationships to `User` and `Pc`.

- [ ] **Step 3: Run migrations**
Run: `docker compose exec app php artisan migrate`
Expected: Tables created in PostgreSQL.

- [ ] **Step 4: Commit**
```bash
git add app/Models database/migrations
git commit -m "- database schema and eloquent models"
```

---

### Task 3: JWT Authentication System

**Files:**
- Modify: `composer.json`
- Create: `app/Http/Controllers/Api/v1/AuthController.php`
- Create: `app/Http/Services/AuthService.php`
- Create: `app/Http/Resources/UserResource.php`
- Modify: `config/auth.php`

- [ ] **Step 1: Install and configure JWT package**
Run: `composer require php-open-source-saver/jwt-auth`
Run: `php artisan jwt:secret`

- [ ] **Step 2: Implement User JWT traits**
Implement `JWTSubject` in `User` model.

- [ ] **Step 3: Implement AuthService and AuthController**
Add `login`, `register`, `logout`, `me` methods.

- [ ] **Step 4: Verify Auth via Postman/Curl**
Expected: Login returns JWT token.

- [ ] **Step 5: Commit**
```bash
git add .
git commit -m "- jwt authentication system"
```

---

### Task 4: PC Discovery and Data Sync Service

**Files:**
- Create: `app/Http/Services/PcService.php`
- Create: `app/Http/Services/SyncService.php`
- Create: `app/Http/Controllers/Api/v1/SyncController.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Implement PcService**
Logic to find or create PC by `unique_id` for a given user.

- [ ] **Step 2: Implement SyncService for Batch Processing**
Logic to bulk insert `Processes` and `Schedules`.

- [ ] **Step 3: Implement SyncController endpoints**
`POST /api/v1/sync/processes` and `POST /api/v1/sync/schedules`.

- [ ] **Step 4: Add API Routes**
Register routes under `v1` prefix with `auth:api` middleware.

- [ ] **Step 5: Commit**
```bash
git add .
git commit -m "- pc discovery and batch sync api"
```

---

### Task 5: Web Dashboard (Blade)

**Files:**
- Create: `resources/views/dashboard.blade.php`
- Create: `app/Http/Controllers/Web/DashboardController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Implement Dashboard Controller**
Fetch logs for the authenticated user, grouped by PC.

- [ ] **Step 2: Create Blade Views**
List PCs and show activity tables.

- [ ] **Step 3: Implement Admin View**
Middleware check for `role === 'admin'` to see all data.

- [ ] **Step 4: Commit**
```bash
git add .
git commit -m "- web dashboard with blade templates"
```

---

### Task 6: API Documentation (Swagger)

**Files:**
- Create: `app/Documentation.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Setup L5-Swagger**
Run: `composer require darkaonline/l5-swagger`

- [ ] **Step 2: Add OpenAPI annotations to Controllers**
Document login and sync endpoints.

- [ ] **Step 3: Generate Docs**
Run: `php artisan l5-swagger:generate`
Expected: `http://localhost:8080/api/documentation` is functional.

- [ ] **Step 4: Commit**
```bash
git add .
git commit -m "- api documentation with swagger"
```
