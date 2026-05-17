# Docker Deployment Verification Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Verify and improve the Docker deployment process, ensuring seamless "first run" initialization and accurate documentation.

**Architecture:** We will modify `docker-entrypoint.sh` to handle automatic migrations and update `README.md` to include all necessary configuration steps. We will also ensure `db.env` is correctly documented and used.

**Tech Stack:** Docker, Docker Compose, Laravel 12, PostgreSQL 18.

---

### Task 1: Research and Reproduction

**Files:**
- Modify: `README.md`
- Modify: `docker-compose.yml`
- Create: `db.env.example`

- [ ] **Step 1: Verify current failure state of fresh deployment**
Try to simulate a fresh start by checking if `.env` and `db.env` exist.
Run: `ls .env db.env`
Expected: If they don't exist, the `docker-compose up` will fail or database won't be reachable.

- [ ] **Step 2: Create `db.env.example`**
Create a template for database environment variables.
```bash
POSTGRES_USER=user
POSTGRES_PASSWORD=password
POSTGRES_DB=logger_db
```

- [ ] **Step 3: Update `docker-compose.yml` to support `initdb.sh` (optional but good for robustness)**
Check if we need `initdb.sh`. Actually, if we use `POSTGRES_DB` etc. in `db.env`, Postgres image handles basic init. `initdb.sh` might be redundant or for advanced setup. I'll stick to standard env vars for now.

### Task 2: Automate Database Initialization

**Files:**
- Modify: `docker/docker-entrypoint.sh`

- [ ] **Step 1: Add migration logic to `docker-entrypoint.sh`**
We want to run `php artisan migrate --force` if the database is ready.
```bash
# Add to docker-entrypoint.sh before exec
if [ -f "./artisan" ]; then
    echo "Running migrations..."
    php artisan migrate --force || echo "Migration failed, maybe DB is not ready yet"
fi
```

- [ ] **Step 2: Improve DB readiness check**
Wait for the database to be available before migrating.

### Task 3: Update Documentation (README.md)

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Add `db.env` instructions**
Update the "Setup Environment" section to include creating `db.env`.

- [ ] **Step 2: Clarify automatic migration**
Mention that migrations now run automatically on startup.

- [ ] **Step 3: Verify all access points and commands**
Ensure ports and links in README match `docker-compose.yml`.

### Task 4: Final Verification

- [ ] **Step 1: Test full deployment flow**
1. `cp .env.example .env`
2. `cp db.env.example db.env`
3. `docker-compose up -d`
4. Verify DB tables exist.
5. `docker-compose down`
6. `docker-compose up -d`
7. Verify app still works.
