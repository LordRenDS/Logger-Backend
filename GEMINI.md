# APPLICATION
A client-server application that records activity on the user's PC and allows you to analyze what the user was doing and when.
The client-side component is a WinForms application that typically runs in the background and periodically determines which process is currently active and the title of its window. It also records when the PC is turned on or off. The application window (usually hidden in the system tray) includes a DataGridView displaying the information collected so far. When the currently active process changes (after all, we don’t switch between windows every second), a set of data (date, time, process name, window title) is recorded locally (in the application’s own database, using SQLite) and sent to the server.
The server-side component is a Laravel-based REST API and web dashboard designed to handle multi-user activity tracking across numerous devices. It serves as a centralized repository that receives, validates, and stores data from WinForms client instances. The server employs an optimized synchronization service that processes batch uploads of activity logs (including process names, window titles, and durations) and PC power state history (on/off events). Built with a Controller-Service-Model architecture, it ensures a clean separation of concerns and scalability. Security is enforced through JWT-based authentication for the API and session-based authentication for the web dashboard, where users can monitor their PCs' real-time status and browse historical data. The system also includes automated device discovery, mapping each PC to its owner, and comprehensive OpenAPI documentation for easy client integration.
The server is built using **PHP 8.4 (Laravel 12)** and **PostgreSQL 18**.
**Current Server State:**
- **Security**: JWT-based authentication for API and session-based for the dashboard.
- **Architecture**: Controller-Service-Model pattern for clean business logic separation.
- **Data Sync**: Optimized batch ingestion for PC activity logs (processes and status changes) with device ownership validation.
- **PC Management**: Auto-discovery of new devices and tracking of online/offline status via `last_seen_at`.
- **Monitoring**: Blade-based dashboard for real-time activity overview and historical analysis.
- **Documentation**: Built-in OpenAPI 3.1 (Swagger) documentation for API integration.
- **Deployment**: Fully dockerized environment (Apache, PHP-FPM, PostgreSQL). Requires `db.env` for database and admin account auto-creation.

# PROJECT NUANCES & PITFALLS
You should take these nuances into account during development, update them as needed, and add new notes when encountering special, unusual, or complex cases.
### 1. Environment Variables vs. Config Caching (Docker)
In a production-like Docker environment, Laravel's `config:cache` is often run during the entrypoint. When config is cached, `env()` calls outside of configuration files return `null`.
- **Pitfall:** `DatabaseSeeder` originally failed to create the admin because it used `env('ADMIN_EMAIL')` directly.
- **Solution:** Admin credentials were moved to `config/app.php` and accessed via `config('app.admin.email')`.

### 2. Session Permissions in Docker
The `app` container runs Apache/PHP-FPM as the `www-data` user, but commands run via `docker compose exec` often run as `root` by default.
- **Pitfall:** Running tests or commands as `root` can create session or cache files that `www-data` cannot later overwrite, leading to 500 errors or "Permission Denied" warnings.
- **Nuance:** Always ensure `storage` and `bootstrap/cache` have `775` permissions and are owned by `www-data`. Use `docker compose exec -u www-data app ...` for application commands to maintain consistency.

### 3. Hybrid Authentication Guard Conflicts (JWT + Session)
The project uses `web` (Session) for the dashboard and `api` (JWT) for WinForms clients.
- **Pitfall:** Standard Laravel Breeze tests may fail in the Docker environment if the `AUTH_GUARD` isn't explicitly managed or if CSRF/Session state is lost between different auth mechanisms.
- **Nuance:** Testing session-based authentication in a hybrid environment requires careful session management. Manual verification of the database (`psql`) is often more reliable than automated session tests in complex Docker setups.

### 4. Docker Compose Environment Sharing
By default, services in Docker Compose don't share environment variables unless explicitly linked.
- **Nuance:** The `app` service must explicitly include `db.env` in its `env_file` list to access credentials shared with the database service.

# COMMIT MESSAGE
Write commits only as a list with a '-' separator at the beginning, each change on a new line and in lowercase.
Do not write changes in each file if you can logically group changes from several files. Use the English language.