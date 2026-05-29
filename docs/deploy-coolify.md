# Deploying Logger Backend with Coolify

This guide explains how to deploy the **Logger Backend** application to a server using **Coolify**, a self-hosted Heroku/Netlify alternative.

Coolify allows you to deploy applications using Docker, Dockerfile, or Docker Compose. This project is configured to run out-of-the-box in Coolify using its **Docker Compose** integration.

---

## 📋 Prerequisites

1. A server with **Coolify** installed and running.
2. A GitHub/GitLab repository containing your **Logger Backend** code.
3. Domain name(s) configured and pointed to your server's IP address (for HTTPS/reverse proxy).

---

## 🚀 Step-by-Step Deployment

### Step 1: Create a New Project in Coolify
1. Navigate to your Coolify dashboard.
2. Click on **Projects** in the sidebar, and then click **+ Add**.
3. Name your project (e.g., `Logger Suite`) and select an environment (e.g., `production`).

### Step 2: Add a Source (Git Repository)
1. Within your project, click **+ Add Resource** and choose **Public Repository** or **Private Repository** (depending on where your code lives).
2. Connect your Git repository (GitHub/GitLab) and select the `main` or `dev` branch.
3. Select **Docker Compose** as the Build Pack. Coolify will automatically read the base `docker-compose.yml` file from the root of your project.

### Step 3: Configure Database (Two Options)

You can run PostgreSQL in two ways:

#### Option A: Coolify's Standalone PostgreSQL (Recommended)
This is the recommended approach for production because Coolify manages backups, logs, and metrics for you.
1. Click **+ Add Resource** -> **PostgreSQL**.
2. Set a name (e.g., `logger-db`) and complete the database setup. Coolify will generate secure credentials.
3. Once deployed, note down the internal connection URI or the host name provided by Coolify (e.g., `postgresql-12345:5432`).
4. In your application settings inside Coolify, navigate to the **Docker Compose** view and **delete** the `db` service definition to avoid launching a duplicate database container.

#### Option B: Embedded PostgreSQL (Inside Compose)
Keep the database service as defined in `docker-compose.yml`. Coolify will provision a Docker volume automatically to persist PostgreSQL data.
1. Make sure to define the credentials using **Environment Variables** in the Coolify UI (see next step).

---

### Step 4: Configure Environment Variables

Coolify overrides system and file-level `.env` variables via its web UI. Navigate to the **Environment Variables** tab of your application in Coolify and configure the following:

#### Required Base Variables:
| Key | Value | Notes |
|---|---|---|
| `APP_NAME` | `Logger Backend` | Application display name |
| `APP_ENV` | `production` | Enables production optimizations and security |
| `APP_DEBUG` | `false` | **CRITICAL:** Must be `false` in production for safety |
| `APP_URL` | `https://logger.yourdomain.com` | Your public production URL |
| `APP_PORT` | `80` | Internal port mapped inside the container |
| `JWT_SECRET` | *(Generate a secure key)* | Sign JWT tokens for WinForms clients. Alternatively, leave it blank; the `docker-entrypoint.sh` will auto-generate it. |
| `APP_KEY` | *(Generate a secure base64 key)* | Encryption key. Alternatively, leave it blank; the `docker-entrypoint.sh` will auto-generate it. |

#### Database Connection (Must match your PostgreSQL database):
| Key | Value | Notes |
|---|---|---|
| `DB_CONNECTION` | `pgsql` | PostgreSQL driver |
| `DB_HOST` | `logger-db` *(or service name)* | Hostname. If using Option B, use `db`. If Option A, use the Coolify internal DB service name. |
| `DB_PORT` | `5432` | Internal PostgreSQL port |
| `DB_DATABASE` | `logger_db` | Name of the database |
| `DB_USERNAME` | `user` | Database user name |
| `DB_PASSWORD` | *(Secure password)* | Database password |

#### Auto-Created Admin Credentials:
These credentials are used by the automated DatabaseSeeder on the first launch:
| Key | Value | Notes |
|---|---|---|
| `ADMIN_NAME` | `Admin` | Initial admin's name |
| `ADMIN_EMAIL` | `admin@yourdomain.com` | Dashboard login email |
| `ADMIN_PASSWORD` | *(Secure password)* | Minimum 8 characters. Must be strong! |

---

### Step 5: Configure Networking and Ports in Coolify

Coolify utilizes a reverse proxy (Traefik or Caddy) to route public domain requests to your application container without exposing host ports.

1. In the **General** settings of your application, set the **Domains** field to your public URL (e.g., `https://logger.yourdomain.com`). Coolify will automatically request and renew a free Let's Encrypt SSL certificate.
2. In the **Port Mapping** or **Exposed Port** field, ensure Coolify routes traffic to port **`80`** (which is the port the Apache server listens to inside the container).
3. Do **not** map host ports (like `8080:80`) in Coolify if not necessary, to avoid port conflicts with other apps running on the same server. Coolify handles the routing securely via the internal Docker bridge network.

---

### Step 6: Deploy!

Click the **Deploy** button at the top right of your Coolify console. Coolify will:
1. Pull your Git repository.
2. Build the Docker image using the `docker/php-server.Dockerfile` config.
3. Automatically launch your containers.
4. Execute `docker-entrypoint.sh` inside the `app` container, which safely:
   - Waits for the database to be fully up and running.
   - Run database migrations (`php artisan migrate --force`).
   - Run database seeders (`php artisan db:seed --force`) to create the initial admin user.
   - Clears and caches configuration to maximize performance.

---

## 🛠 Troubleshooting Common Issues in Coolify

### 1. `Database is not ready yet...` or Connection Timeout
- **Cause:** The `app` service cannot reach the PostgreSQL database, or the database container is slow to start.
- **Solution:** Verify the `DB_HOST` matches the exact service name (e.g. `db` for Option B, or the Coolify generated service name like `postgresql-12345` for Option A). Ensure both containers are assigned to the same Docker network in Coolify (usually `coolify` or `default`).

### 2. Session / Cache Permissions Error (500 Internal Server Error)
- **Cause:** Docker volumes or cached files created by commands run as `root` cannot be written to by the `www-data` web server user.
- **Solution:** The build process is already configured to run as `www-data` and sets permissions properly. However, if you run manual commands, ensure you do it as the `www-data` user:
  ```bash
  php artisan cache:clear
  ```
  If permissions are corrupted, run a manual shell inside the container via the Coolify dashboard and reset permissions:
  ```bash
  chown -R www-data:www-data /app/storage /app/bootstrap/cache
  chmod -R 775 /app/storage /app/bootstrap/cache
  ```

### 3. Config Cache Issue (Null Env Values)
- **Cause:** When Laravel caches configuration (`config:cache`), calls to `env()` outside config files return `null`.
- **Solution:** The project's architecture already follows this guideline. DatabaseSeeder uses `config('app.admin.*')` instead of `env('ADMIN_EMAIL')` directly. Avoid modifying the codebase to use `env()` inside controllers, services, or seeders. Always put them in `config/` files first!
