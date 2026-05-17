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
Copy the example environment files and adjust variables if necessary:
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

### 3. Build and Run Containers
```bash
docker-compose up -d --build
```
*Note: The first start will automatically:*
- *Create `.env` if missing.*
- *Generate the `APP_KEY`.*
- *Run `composer install` (during build).*
- *Wait for the database to be ready.*
- *Run migrations and seed the database.*

### 4. Generate API Documentation (Optional)
If not generated automatically, you can run:
```bash
docker-compose exec app php artisan l5-swagger:generate
```

## 🔗 Access Points

- **Dashboard**: [http://localhost:8080/dashboard](http://localhost:8080/dashboard)
- **API Documentation**: [http://localhost:8080/api/documentation](http://localhost:8080/api/documentation)
- **Adminer (DB Management)**: [http://localhost:800](http://localhost:800)
  - *System*: PostgreSQL
  - *Server*: db
  - *Username*: user
  - *Password*: password
  - *Database*: logger_db

## 🧪 Testing

Execute the test suite using PHPUnit inside the container:
```bash
docker-compose exec app php artisan test
```

## 🛠 Troubleshooting (Windows)

### Line Endings
If the `docker-entrypoint.sh` fails with `\r` errors, ensure the file uses **LF** line endings. The Dockerfile includes a `sed` command to fix this automatically during build, but manual conversion might be needed if you edit scripts on the host.

### Permissions
If you encounter 500 errors related to logs or cache, reset permissions:
```bash
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## 📝 Commit Convention
- Use a list with a `-` separator.
- Lowercase only.
- English language.
- Example: `- fixed user factory relationship`
