# Logger Backend

A high-performance Laravel 12 backend for tracking and analyzing PC activity across multiple users and devices.

## 🚀 Features

- **JWT Authentication**: Secure API access for WinForms clients.
- **Batch Synchronization**: Optimized endpoint for high-volume activity log ingestion.
- **Real-time Dashboard**: Blade-based monitoring for PC status and process activity.
- **Auto-Discovery**: Automatic registration of new PCs upon first sync.
- **Swagger API Docs**: Built-in OpenAPI 3.1 documentation.
- **Dockerized**: Pre-configured environment with PHP 8.4 and PostgreSQL 18.

## 🛠 Tech Stack

- **Framework**: Laravel 12.x
- **Language**: PHP 8.4
- **Database**: PostgreSQL 18
- **Documentation**: L5-Swagger (OpenAPI 3.1)
- **Infrastructure**: Docker & Docker Compose

## 📦 Installation

### 1. Prerequisites
- Docker and Docker Compose installed.
- (Windows Users) Git configured to handle line endings correctly or a tool to convert CRLF to LF for scripts.

### 2. Setup Environment
Copy the example environment files and adjust variables if necessary (`db.env` is mandatory for the application and database containers):
```bash
cp .env.example .env
cp db.env.example db.env
```
*Important: Ensure that `DB_PASSWORD` in `.env` matches `POSTGRES_PASSWORD` in `db.env`.*

**Initial Admin Account**:
You can define an initial admin account in `db.env` using `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD`. These will be used by the seeder to create the first admin user automatically.

### Environment Variables (db.env)
| Variable | Description |
|----------|-------------|
| `POSTGRES_USER` | DB user for PostgreSQL |
| `POSTGRES_PASSWORD` | DB password |
| `POSTGRES_DB` | Main database name |
| `ADMIN_NAME` | Name for the auto-created admin |
| `ADMIN_EMAIL` | Email for the admin account |
| `ADMIN_PASSWORD` | Password for the admin account |

### Email Configuration (Password Reset)
Password reset functionality is included by default via Laravel Breeze. However, `MAIL_MAILER` in `.env.example` is set to `log` for local development, which writes emails to `storage/logs/laravel.log` rather than sending them.
To enable real email delivery for password resets, update the `MAIL_*` variables in your `.env` file with proper SMTP credentials:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@example.com"
```

### 3. Build and Run Containers

Depending on whether you are running a local development/testing environment or deploying to a production server, choose one of the options below:

#### Option A: Local Development & Testing (Test Version)
This launches the application with host project directory volume bind-mounts (for live-reload code changes) and spins up **Adminer** for database management:
```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

#### Option B: Production Server (Use Version)
This launches a secure and optimized configuration without mounting host directories (code is loaded directly from the built Docker image) and without starting Adminer:
```bash
docker compose up -d --build
```

#### Option C: Coolify Deployment
If you are deploying using **Coolify**, please read our dedicated guide:
👉 [Coolify Deployment Guide](docs/deploy-coolify.md)

*Note: The first start on any environment will automatically:*
- *Create `.env` if missing.*
- *Generate the `APP_KEY` and `JWT_SECRET`.*
- *Run `composer install` (during build).*
- *Wait for the database to be ready.*
- *Run migrations and seed the database.*

### 4. Generate API Documentation (Optional)
If not generated automatically, you can run:
- **Local Dev:**
  ```bash
  docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app php artisan l5-swagger:generate
  ```
- **Production:**
  ```bash
  docker compose exec app php artisan l5-swagger:generate
  ```

## 🔗 Access Points

- **Dashboard**: [http://localhost:8080/dashboard](http://localhost:8080/dashboard)
- **API Documentation**: [http://localhost:8080/api/documentation](http://localhost:8080/api/documentation)
- **Adminer (DB Management - Local Dev only)**: [http://localhost:800](http://localhost:800)
  - *System*: PostgreSQL
  - *Server*: db
  - *Username*: user
  - *Password*: password
  - *Database*: logger_db

## 🧪 Testing

Execute the test suite using PHPUnit inside the container. It is recommended to run tests as the `www-data` user to avoid permission issues with the cache:
```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec -u www-data app php artisan test
```

## 🛠 Troubleshooting (Windows)

### Line Endings
If the `docker-entrypoint.sh` fails with `\r` errors, ensure the file uses **LF** line endings. The Dockerfile includes a `sed` command to fix this automatically during build, but manual conversion might be needed if you edit scripts on the host.

### Permissions
If you encounter 500 errors related to logs or cache, reset permissions:
- **Local Dev:**
  ```bash
  docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app chown -R www-data:www-data storage bootstrap/cache
  ```
- **Production:**
  ```bash
  docker compose exec app chown -R www-data:www-data storage bootstrap/cache
  ```
