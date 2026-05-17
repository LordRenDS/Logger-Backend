# Application Verification Plan (Docker)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Exhaustively verify the application's deployment and functionality from scratch and during repeated launches using Docker.

**Architecture:** We will use `docker-compose` to manage the environment and `run_shell_command` to execute verification steps. We will simulate a fresh install and a restart scenario.

**Tech Stack:** Docker, Docker Compose, PHPUnit, Laravel.

---

### Task 1: Verification From Scratch (Clean Slate)

**Files:**
- Modify: `.env` (temporary for testing)
- Modify: `db.env` (temporary for testing)

- [ ] **Step 1: Clean up existing environment**
Stop and remove all containers, volumes, and networks associated with the project.
Run: `docker-compose down -v`

- [ ] **Step 2: Setup environment files**
Ensure `.env` and `db.env` are created from examples.
Run: `copy .env.example .env` and `copy db.env.example db.env`

- [ ] **Step 3: Launch containers and wait for initialization**
Start the containers and wait for the `app` container to finish its entrypoint (migrations and seeding).
Run: `docker-compose up -d --build`
Wait: 20-30 seconds.
Check logs: `docker-compose logs app` (look for "Running migrations and seeders...")

- [ ] **Step 4: Verify Database Initialization**
Check if the `pc_statuses` table has the seeded data.
Run: `docker-compose exec app php artisan tinker --execute="print_r(App\Models\PcStatus::all()->toArray());"`
Expected: Output showing 'on' and 'off' statuses.

- [ ] **Step 5: Run Automated Tests**
Run the full test suite inside the container.
Run: `docker-compose exec app php artisan test`
Expected: ALL tests pass.

- [ ] **Step 6: Verify API Health**
Call the health check endpoint.
Run: `curl http://localhost:8080/api/v1/health`
Expected: `{"status":"ok"}`

---

### Task 2: Verification of Persistent Launch (Existing Containers)

- [ ] **Step 1: Restart containers**
Stop the containers (without removing volumes) and start them again.
Run: `docker-compose stop` then `docker-compose start`

- [ ] **Step 2: Check logs for idempotency**
Verify that the entrypoint handles the existing state correctly (no errors during "Running migrations...").
Run: `docker-compose logs app`

- [ ] **Step 3: Verify data persistence**
Ensure the data from Task 1 (statuses) is still there.
Run: `docker-compose exec app php artisan tinker --execute="echo App\Models\PcStatus::count();"`
Expected: `2`

- [ ] **Step 4: Verify Web Interface Accessibility**
Check if the dashboard returns a 200/302 (redirect to login if not auth).
Run: `curl -I http://localhost:8080/dashboard`
Expected: `HTTP/1.1 302 Found` (redirect to login) or `200 OK` (if welcome page).

---

### Task 3: Cleanup

- [ ] **Step 1: Final Cleanup**
Optionally clean up the test environment or leave it running if the user wants to explore.
Run: `docker-compose down`
